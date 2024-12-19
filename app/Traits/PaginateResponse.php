<?php

namespace App\Traits;

trait PaginateResponse
{
    /**
     * paginate response
     *
     * @param [type] $model
     * @param string $message
     * @return void
     */
    public function paginateResponse($model, $message = 'Successfully')
    {
        $pagination = [
            'total'         => $model->total(),
            'current_page'  => $model->currentPage(),
            'per_page'      => $model->perPage(),
            'last_page'     => $model->lastPage(),
            'from'          => $model->firstItem(),
            'to'            => $model->lastItem(),
        ];

        if (isset($model->total_roles)) {
            $pagination['total_roles'] = $model->total_roles;
        }

        return response()->json([
            'status'    => true,
            'success'   => true,
            'message'   => $message,
            'data'      => $model->items(),
            'pagination' => $pagination,
        ]);
    }
}
