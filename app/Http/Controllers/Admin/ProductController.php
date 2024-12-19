<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductImage;
use App\Traits\PaginateResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{

    use PaginateResponse;

    public function create(Request $request)
    {
        DB::beginTransaction();


        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:products,name',
                'category_id' => 'required|exists:categories,id',
                'description' => 'required|string|max:2000',
                'sku' => 'required|string|unique:products,sku',
                'image' => 'required|mimes:jpeg,png,jpg,webp|max:2040',
                'condition' => 'required|in:new,second',
                'price_id' => 'required|exists:prices,id',
                'weight' => 'required|int',
                'price' => 'required|int',
                'discount' => 'nullable|int',
                'minimal_order' => 'required|int',
                'status' => 'required|in:ready stock,preorder'
            ]);

            $params = $validator->validate();

            $categoryId = $params['category_id'];
            $subcategoryId = null;

            if ($categoryId) {
                $category = Category::find($categoryId);

                $childCategories = Category::where('parent_id', $categoryId)->count();

                if ($childCategories > 0) {
                    $subcategoryId = $categoryId;
                    $categoryId = $category->parent_id;
                }
            }

            $product = Product::create([
                'name' => $params['name'],
                'slug' => Str::slug($params['name']),
                'category_id' => $categoryId,
                'subcategory_id' => $subcategoryId,
                'sku' => $params['sku'],
                'condition' => $params['condition'],
                'price_id' => $params['price_id'],
                'weight' => $params['weight'],
                'price' => $params['price'],
                'discount' => $params['discount'] ?? null,
                'minimal_order' => $params['minimal_order'],
                'status' => $params['status'],
            ]);

            $file = $request->file('image');
            $path = 'uploads/category';
            $fileName = UploadHelper::uploadFile($file, $path);
            $fileType = $file->getMimeType();

            ProductImage::create([
                'product_id' => $product->id,
                'url' => $fileName,
                'file_type' => str_contains($fileType, 'image') ? 'image' : 'video',
            ]);

            if ($params['description']) {
                ProductDescription::create([
                    'product_id' => $product->id,
                    'description' => $params['description']
                ]);
            }

            DB::commit();

            $product->description = ProductDescription::where('product_id', $product->id)->get('description');
            $product->images = ProductImage::where('product_id', $product->id)->get('url');
            $product->makeHidden(['created_at', 'updated_at']);

            return response()->api($product, 200);
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
            $product = Product::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'string|unique:products,name,' . $product->id,
                'category_id' => 'required|exists:categories,id',
                'description' => 'string|max:2000',
                'condition' => 'in:new,second',
                'price_id' => 'exists:prices,id',
                'weight' => 'int',
                'price' => 'int',
                'discount' => 'nullable|int',
                'minimal_order' => 'int',
                'status' => 'in:ready stock,preorder',
                'image' => 'nullable|mimes:jpeg,png,jpg,webp|max:2040',
            ]);

            $params = $validator->validate();

            $categoryId = $params['category_id'];
            $subcategoryId = null;

            if ($categoryId) {
                $category = Category::find($categoryId);

                $childCategories = Category::where('parent_id', $categoryId)->count();

                if ($childCategories > 0) {
                    $subcategoryId = $categoryId;
                    $categoryId = $category->parent_id;
                }
            }

            $product->update([
                'name' => $params['name'],
                'slug' => Str::slug($params['name']),
                'category_id' => $categoryId,
                'subcategory_id' => $subcategoryId,
                'condition' => $params['condition'],
                'price_id' => $params['price_id'],
                'weight' => $params['weight'],
                'price' => $params['price'],
                'discount' => $params['discount'] ?? null,
                'minimal_order' => $params['minimal_order'],
                'status' => $params['status'],
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = 'uploads/category';
                $fileName = UploadHelper::uploadFile($file, $path);
                $fileType = $file->getMimeType();

                ProductImage::where('product_id', $product->id)->delete(); 
                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $fileName,
                    'file_type' => str_contains($fileType, 'image') ? 'image' : 'video',
                ]);
            }

            if ($params['description']) {
                $productDescription = ProductDescription::updateOrCreate(
                    ['product_id' => $product->id],
                    ['description' => $params['description']]
                );
            }

            DB::commit();

            // Return produk dengan deskripsi dan gambar
            $product->description = ProductDescription::where('product_id', $product->id)->get('description');
            $product->images = ProductImage::where('product_id', $product->id)->get('url');
            $product->makeHidden(['created_at', 'updated_at']);

            return response()->api($product, 200);
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
                'per_page' => 'nullable|numeric',
                'q' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
                'status' => 'nullable|in:ready stock,preorder',
                'condition' => 'nullable|in:new,second',
            ]);

            $params = $validator->validate();
            $search = $params['q'] ?? null;
            $perPage = $params['per_page'] ?? 10;
            $categoryId = $params['category_id'] ?? null;
            $status = $params['status'] ?? null;
            $condition = $params['condition'] ?? null;

            $products = Product::select([
                'id',
                'sku',
                'name',
                'category_id',
                'price',
                'weight',
                'price_id',
                'discount',
                'minimal_order',
                'condition',
                'status'
            ])
                ->when(
                    !is_null($search),
                    fn($q) =>
                    $q->where(
                        fn($q) =>
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('sku', 'like', "%$search%")
                    )
                )
                ->when(
                    !is_null($categoryId),
                    fn($q) =>
                    $q->where('category_id', $categoryId)
                )
                ->when(
                    !is_null($status),
                    fn($q) =>
                    $q->where('status', $status)
                )
                ->when(
                    !is_null($condition),
                    fn($q) =>
                    $q->where('condition', $condition)
                )
                ->orderByDesc('created_at')
                ->with(['category:id,name', 'images:product_id,url'])
                ->paginate($perPage);


            return $this->paginateResponse($products, 'success');
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
            $product = Product::select([
                'id',
                'name',
                'sku',
                'category_id',
                'price',
                'weight',
                'price_id',
                'discount',
                'point',
                'minimal_order',
                'condition',
                'status',
            ])
                ->with(['category:id,name', 'description:product_id,description', 'images:product_id,url', 'level:id,level'])
                ->find($id);

            if (!$product) {
                throw new \Exception('Product not found', 404);
            }

            $product->makeHidden('category_id', 'product_id', 'price_id');

            return response()->api($product, 200);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => [
                    'code' => $statusCode,
                    'message' => $e->getMessage(),
                ]
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
                'status' => 'nullable|in:ready stock,preorder',
                'condition' => 'nullable|in:new,second',
            ]);

            $params = $validator->validate();
            $search = $params['q'] ?? null;
            $limit = $params['limit'] ?? 10;
            $status = $params['status'] ?? null;
            $condition = $params['condition'] ?? null;

            $products = Product::select([
                'id',
                'name',
                'sku',
                'category_id',
                'price',
                'weight',
                'price_id',
                'discount',
                'minimal_order',
                'condition',
                'status',
            ])
                ->when(
                    !is_null($search),
                    fn($q) =>
                    $q->where(
                        fn($q) =>
                        $q->where('name', 'like', "%$search%")
                    )
                )
                ->when(
                    !is_null($status),
                    fn($q) =>
                    $q->where('status', $status)
                )
                ->when(
                    !is_null($condition),
                    fn($q) =>
                    $q->where('condition', $condition)
                )
                ->with(['category:id,name', 'images:product_id,url'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            return response()->api($products, 200);
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

    public function delete(string $id)
    {
        DB::beginTransaction();

        try {
            $product = Product::select([
                'id',
                'name',
                'sku'
            ])
                ->find($id);

            if (!$product) {
                throw new \Exception('Product not found', 404);
            }

            $orderCount = Order::where('product_id', $id)->count();
            if ($orderCount > 0) {
                throw new \Exception('Product has been ordered and cannot be deleted', 400);
            }

            $deleted = $product->delete();

            DB::commit();

            return response()->api($deleted, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;

            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }
}
