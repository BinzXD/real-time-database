<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use App\Helpers\UploadHelper;
use App\Http\Requests\AllRequest;
use App\Http\Requests\ListRequest;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Http\Requests\Bank\BankStoreRequest;
use App\Http\Requests\Bank\BankUpdateRequest;


class BankController extends Controller
{
    public function index(ListRequest $request)
    {
        try {
            $params = $request->validated();
            $search = $params['q'] ?? null;
            $perPage = $params['per_page'] ?? 10;
            $orderBy = $params['order_by'] ?? 'created_at';
            $orderDirection = $params['order_direction'] ?? 'desc';

            $data = Bank::select([
                'id',
                'icon',
                'code',
                'name',
                'alias',
                'status'
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

            $data = Bank::select([
                'id',
                'icon',
                'code',
                'name',
                'alias',
                'status'
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
            $data = Bank::select([
                'id',
                'icon',
                'code',
                'name',
                'alias',
                'status'
            ])->find($id);

            if (!$data) {
                throw new \Exception('Bank not found!', 404);
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

    public function store(BankStoreRequest $request)
    {
        try {
            $params = $request->validated();

            $file = $request->file('icon');
            $path = 'uploads/banks';
            $fileName = UploadHelper::uploadFile($file, $path);

            $params['icon'] = $fileName;
            $data = Bank::create($params);

            return response()->api($data, 200);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function update(BankUpdateRequest $request, string $id)
    {
        try {
            $params = $request->validated();
            $data = Bank::find($id);
            if (!$data) {
                throw new \Exception('Bank not found!', 404);
            }

            if ($request->hasFile('icon')) {
                UploadHelper::deleteFile('uploads/banks/' . $data->icon);
                $file = $request->file('icon');
                $path = 'uploads/banks';
                $fileName = UploadHelper::uploadFile($file, $path);
                $params['icon'] = $fileName;
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
            $data = Bank::find($id);

            if (!$data) {
                throw new \Exception('Bank not found!', 404);
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

    public function set_status(string $id)
    {
        try {
            $data = Bank::find($id);

            if (!$data) {
                throw new \Exception('Bank not found!', 404);
            }

            $data->status = $data->status == 'active' ? 'inactive' : 'active';
            $data->save();
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
