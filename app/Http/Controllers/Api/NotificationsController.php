<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Traits\ResponseApi;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    use ResponseApi;

    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();
            $perPage = $request->query('per_page', 15);
            $search = $request->input('search');

            $notifications = $user->notifications()
                ->when($search, function ($query) use ($search) {
                    $query->where('data->message', 'like', '%' . $search . '%');
                })
                ->latest()
                ->paginate($perPage);

            // return NotificationResource::collection($notifications);
            return response()->json([
                'result' => true,
                'message' => 'تم استرجاع الاشعارات بنجاح',
                'data' => $notifications,
            ]);
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }

    public function index1(Request $request)
    {
        try {
            $user = auth('api')->user();
            $perPage = $request->query('per_page', 15);
            $search = $request->input('search');
            // dd($search);
            $notifications = $user->notifications()
                ->when($search, function ($query) use ($search) {
                    $query->where('data->message', 'like', '%' . $search . '%');
                })
                ->latest()
                ->paginate($perPage);

            // return NotificationResource::collection($notifications);
            return response()->json([
                'result' => true,
                'message' => 'تم استرجاع الاشعارات بنجاح',
                'data' => $notifications,
            ]);
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }

    // public function index(Request $request)
    // {
    //     try {
    //         $user = auth('api')->user();

    //         $types = [
    //             'new_client' => [
    //                 \App\Notifications\NewClientAddedNotification::class,
    //             ],
    //             'unpaid_invoice' => [
    //                 \App\Notifications\InvoiceReminderNotification::class,
    //             ],
    //             'invoice_activity' => [
    //                 \App\Notifications\InvoicePaidNotification::class,
    //                 \App\Notifications\InvoiceRedoNotification::class,
    //             ],
    //             'account_activity' => [
    //                 \App\Notifications\AccountTransferNotification::class,
    //                 \App\Notifications\AccountTransferRedoNotification::class,
    //             ],
    //         ];

    //         $allNotifications = $user->notifications()->latest()->get();

    //         $grouped = [];

    //         foreach ($types as $key => $typeClasses) {
    //             $filtered = $allNotifications->filter(function ($notification) use ($typeClasses) {
    //                 return in_array($notification->type, $typeClasses);
    //             });

    //             $grouped[$key] = NotificationResource::collection($filtered)->values();
    //         }

    //         return $this->responseApi($grouped, 'تم استرجاع الاشعارات بنجاح');

    //     } catch (\Exception $e) {
    //         return $this->responseApiError('حدث خطأ ما.');
    //     }
    // }
}
