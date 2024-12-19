<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AllRequest;
use App\Http\Requests\ListRequest;
use App\Http\Requests\Sosmed\SosmedStoreRequest;
use App\Http\Requests\Sosmed\SosmedUpdateRequest;
use App\Models\SocialMedia;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    public function index(ListRequest $request)
    {
        try {
            $params = $request->validated();
            $search = $params['q'] ?? null;
            $perPage = $params['per_page'] ?? 10;
            $orderBy = $params['order_by'] ?? 'created_at';
            $orderDirection = $params['order_direction'] ?? 'desc';

            $data = SocialMedia::select([
                'id',
                'logo',
                'name',
                'link',
            ])
                ->when(
                    !is_null($search),
                    fn($q) => $q->where('name', 'like', "%$search%")
                )
                ->orderBy($orderBy, $orderDirection)
                ->paginate($perPage);

            return response()->api($data, 200);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function all(AllRequest $request)
    {
        try {
            $params = $request->validated();
            $search = $params['q'] ?? null;
            $limit = $params['limit'] ?? 10;
            $orderBy = $params['order_by'] ?? 'created_at';
            $orderDirection = $params['order_direction'] ?? 'desc';

            $data = SocialMedia::select([
                'id',
                'logo',
                'name',
                'link',
            ])->when(
                !is_null($search),
                fn($q) => $q->where('name', 'like', "%$search%")
            )->limit($limit)->orderBy($orderBy, $orderDirection)->get();

            return response()->api($data, 200);
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
            $data = SocialMedia::select([
                'id',
                'logo',
                'name',
                'link'
            ])->find($id);

            if (!$data) {
                throw new \Exception('Social media not found!', 404);
            }

            return response()->api($data, 200);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function store(SosmedStoreRequest $request)
    {
        try {
            $params = $request->validated();

            $file = $request->file('logo');
            $path = 'uploads/social_media';
            $fileName = UploadHelper::uploadFile($file, $path);

            $params['logo'] = $fileName;
            $data = SocialMedia::create($params);

            return response()->api($data, 200);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function update(SosmedUpdateRequest $request, string $id)
    {
        try {
            $params = $request->validated();
            $data = SocialMedia::find($id);
            if (!$data) {
                throw new \Exception('Social media not found!', 404);
            }

            if ($request->hasFile('logo')) {
                UploadHelper::deleteFile('uploads/social_media/' . $data->logo);
                $file = $request->file('logo');
                $path = 'uploads/social_media';
                $fileName = UploadHelper::uploadFile($file, $path);
                $params['logo'] = $fileName;
            }

            $data->update($params);
            return response()->api($data, 200);
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
            $data = SocialMedia::find($id);

            if (!$data) {
                throw new \Exception('Social media not found!', 404);
            }

            $data->delete();
            return response()->api(null, 200);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }
}
