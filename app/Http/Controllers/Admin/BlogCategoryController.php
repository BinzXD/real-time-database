<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BlogCategoryController extends Controller
{
    public function list(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'per_page' => 'nullable|numeric',
                'q' => 'nullable|string',
            ]);

            $params = $validator->validate();
            $search = $params['q'] ?? null;
            $perPage = $params['per_page'] ?? 10;

            $categories = BlogCategory::select([
                'id',
                'name',
                'slug'
            ])
                ->when(
                    !is_null($search),
                    fn($q) =>
                    $q->where(
                        fn($q) =>
                        $q->where('name', 'like', "%$search%")
                    )
                )
                ->paginate($perPage);

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

            $categories = BlogCategory::select([
                'id',
                'name',
            ])
                ->when(
                    !is_null($search),
                    fn($q) =>
                    $q->where(
                        fn($q) =>
                        $q->where('name', 'like', "%$search%")
                    )
                )
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
            $category = BlogCategory::select([
                'id',
                'name',
                'slug'
            ])
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

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:blog_categories,name',
            ]);

            $params = $validator->validate();

            $params['slug'] = Str::slug($params['name']);

            $category = BlogCategory::create($params);

            return response()->api($category, 200);
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

    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:blog_categories,name,' . $id,
            ]);

            $params = $validator->validate();
            $category = BlogCategory::find($id);
            if (!$category) {
                throw new \Exception('Category not found', 404);
            }

            $category->update($params);

            return response()->api($category, 200);
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
        try {
            $category = BlogCategory::select([
                'id',
                'name',
            ])
                ->find($id);

            if (!$category) {
                throw new \Exception('Category not found', 404);
            }

            $deleted = $category->delete();

            return response()->api($deleted, 200);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }
}
