<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Controller;
use App\Helpers\UploadHelper;
use App\Models\Category;
use App\Models\Product;
use App\Traits\PaginateResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CategoryProductController extends Controller
{
    use PaginateResponse;

    public function create(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:categories,name',
                'image' => 'required|mimes:jpeg,png,jpg,webp|max:2040',
                'sub_category' => 'nullable|array',
                'sub_category.*' => 'nullable|string|distinct|unique:categories,name',
            ]);

            $params = $validator->validate();

            $file = $request->file('image');
            $path = 'uploads/category';
            $fileName = UploadHelper::uploadFile($file, $path);

            $category = Category::create([
                'name' => $params['name'],
                'image' => $fileName,
                'slug' => Str::slug($params['name']),
            ]);

            if (!empty($params['sub_category'])) {
                foreach ($params['sub_category'] as $subCategoryName) {
                    Category::create([
                        'name' => $subCategoryName,
                        'parent_id' => $category->id,
                        'image' => $fileName,
                        'slug' => Str::slug($subCategoryName),
                    ]);
                }
            }

            DB::commit();

            $response = Category::with('subCategories')->find($category->id);
            $response->makeHidden(['parent_id', 'created_at', 'updated_at', 'deleted_at']);
            $response->subCategories->makeHidden(['id', 'parent_id', 'created_at', 'updated_at', 'deleted_at']);

            return response()->api($response, 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            $e = new MessageBag($e->errors());
            $response = [
                'errors' => [
                    'code' => 422,
                    'message' => $e->first(),
                ]
            ];
            return response()->api($response, 422);
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $category = Category::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'string|unique:categories,name,' . $category->id,
                'image' => 'mimes:jpeg,png,jpg,webp|max:2040',
                'sub_category' => 'array',
                'sub_category.*' => 'string|distinct|unique:categories,name',
            ]);

            $params = $validator->validate();

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = 'uploads/category';
                $fileName = UploadHelper::uploadFile($file, $path);

                if ($category->image) {
                    UploadHelper::deleteFile($category->image);
                }

                $category->image = $fileName;
            }

            $category->update([
                'name' => $params['name'],
                'slug' => Str::slug($params['name']),
                'image' => $category->image ?? $category->getOriginal('image'),
            ]);

            if (isset($params['sub_category'])) {
                Category::where('parent_id', $category->id)->delete();

                foreach ($params['sub_category'] as $subCategoryName) {
                    Category::create([
                        'name' => $subCategoryName,
                        'parent_id' => $category->id,
                        'image' => $category->image,
                        'slug' => Str::slug($subCategoryName),
                    ]);
                }
            }

            DB::commit();

            $category = Category::select(['id', 'name', 'image', 'slug'])
                ->with(['subCategories' => function ($query) {
                    $query->select(['id', 'name', 'parent_id', 'image', 'slug']);
                }])
                ->find($category->id);

            return response()->api($category, 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            $e = new MessageBag($e->errors());
            $response = [
                'errors' => [
                    'code' => 422,
                    'message' => $e->first(),
                ]
            ];
            return response()->api($response, 422);
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function list(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'nullable|numeric',
                'q' => 'nullable|string',
            ]);

            $params = $validator->validate();
            $search = $params['q'] ?? null;
            $limit = $params['limit'] ?? 10;

            $categories = Category::select([
                'id',
                'image',
                'name',
                'slug',
            ])
                ->when(
                    !is_null($search),
                    fn($q) =>
                    $q->where(
                        fn($q) =>
                        $q->where('name', 'like', "%$search%")
                    )
                )
                ->orderByDesc('created_at')
                ->limit($limit)
                ->paginate();

            $categories->each(function ($category) {
                $category->subCategories->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at']);
            });

            return $this->paginateResponse($categories, 'success');
        } catch (ValidationException $e) {
            $e = new MessageBag($e->errors());
            $response = [
                'errors' => [
                    'code' => 422,
                    'message' => $e->first(),
                ]
            ];
            return response()->api($response, 422);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function all(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'nullable|numeric',
                'q' => 'nullable|string',
            ]);
            $params = $validator->validate();
            $search = $params['q'] ?? null;
            $limit = $params['limit'] ?? 10;


            $categories = Category::select([
                'id',
                'name',
                'image',
                'slug',
            ])
                ->with(['subCategories' => function ($query) {
                    $query->select(['id', 'name', 'parent_id', 'image', 'slug']);
                }])
                ->when(
                    !is_null($search),
                    fn($q) =>
                    $q->where(
                        fn($q) =>
                        $q->where('name', 'like', "%$search%")
                    )
                )
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            return response()->api($categories, 200);
        } catch (ValidationException $e) {
            $e = new MessageBag($e->errors());
            $response = [
                'errors' => [
                    'code' => 422,
                    'message' => $e->first(),
                ]
            ];
            return response()->api($response, 422);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function show(string $id)
    {
        try {
            $category = Category::select(['id', 'name', 'image', 'slug'])
                ->with(['subCategories' => function ($query) {
                    $query->select(['id', 'name', 'parent_id', 'image', 'slug']);
                }])
                ->find($id);

            if (!$category) {
                throw new \Exception('Category not found', 404);
            }

            return response()->api($category, 200);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $category = Category::find($id);

            if (!$category) {
                throw new \Exception('Category not found', 404);
            }

            $productCount = Product::where('category_id', $id)->count();

            if ($productCount > 0) {
                throw new \Exception('Category is used in products', 400);
            }

            Category::where('parent_id', $id)->delete();

            Category::where('id', $id)->delete();

            DB::commit();

            return response()->api('Success delete category', 200);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function sequence()
    {
        try {
            $categories = Category::select([
                'id',
                'image',
                'name',
                'slug',
            ])
                ->orderByDesc('name')
                ->with(['category:id,name', 'images:product_id,url'])
                ->get();

            return response()->api($categories, 'success');
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }
}
