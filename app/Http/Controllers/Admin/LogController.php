<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Log;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LogController extends Controller
{
    protected $admin_view = 'dashbord.logs';

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Log::with('user')
                ->select('logs.*');

            // Apply filters
            if ($request->has('action') && !empty($request->action)) {
                $query->where('action', $request->action);
            }

            if ($request->has('user_id') && !empty($request->user_id)) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('model_type') && !empty($request->model_type)) {
                $query->where('model_type', $request->model_type);
            }

            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $allData = $query->orderBy('id', 'desc')->get();

            return Datatables::of($allData)
                ->addColumn('id', function ($row) {
                    return $row->id ?? 'N/A';
                })
                ->addColumn('action_type', function ($row) {
                    $action = $row->action ?? 'N/A';
                    $class = match ($action) {
                        'invoice_paid' => 'badge bg-success text-white',
                        'invoice_redo' => 'badge bg-warning text-dark',
                        'invoice_created' => 'badge bg-info text-white',
                        'invoice_deleted' => 'badge bg-danger text-white',
                        'client_created' => 'badge bg-success text-white',
                        'client_updated' => 'badge bg-primary text-white',
                        'client_deleted' => 'badge bg-danger text-white',
                        'clients_imported' => 'badge bg-info text-white',
                        'user_login' => 'badge bg-secondary text-white',
                        'financial_transaction_created' => 'badge bg-success text-white',
                        'financial_transaction_deleted' => 'badge bg-danger text-white',
                        default => 'badge bg-secondary text-white'
                    };

                    $actionLabels = [
                        'invoice_paid' => trans('logs.invoice_paid'),
                        'invoice_redo' => trans('logs.invoice_redo'),
                        'invoice_created' => trans('logs.invoice_created'),
                        'invoice_deleted' => trans('logs.invoice_deleted'),
                        'client_created' => trans('logs.client_created'),
                        'client_updated' => trans('logs.client_updated'),
                        'client_deleted' => trans('logs.client_deleted'),
                        'clients_imported' => trans('logs.clients_imported'),
                        'user_login' => trans('logs.user_login'),
                        'financial_transaction_created' => trans('logs.financial_transaction_created'),
                        'financial_transaction_deleted' => trans('logs.financial_transaction_deleted')
                    ];

                    $label = $actionLabels[$action] ?? $action;
                    return '<span class="' . $class . ' px-4 py-3 rounded-pill fw-bold fs-5">' . $label . '</span>';
                })
                ->addColumn('description', function ($row) {
                    return $row->description ?? 'N/A';
                })
                ->addColumn('user', function ($row) {
                    if ($row->user) {
                        return $row->user->name;
                    }
                    return '<span class="text-muted">System</span>';
                })
                ->addColumn('ip_address', function ($row) {
                    return $row->ip_address ?? 'N/A';
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group btn-group-sm">';

                    $buttons .= '
                        <button type="button" class="btn btn-sm btn-info view-log-details"
                                data-log-id="' . $row->id . '"
                                title="' . trans('logs.view_details') . '" style="font-size: 16px;">
                            <i class="bi bi-eye"></i>
                        </button>';

                    $buttons .= '
                        <a onclick="return confirm(\'' . trans('logs.confirm_delete') . '\')"
                            href="' . route('admin.logs.delete', $row->id) . '"
                            class="btn btn-sm btn-danger" title="' . trans('logs.delete') . '" style="font-size: 16px;">
                            <i class="bi bi-trash3"></i>
                        </a>';

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['action_type', 'user', 'action'])
                ->make(true);
        }

        $data['users'] = Admin::where('status', 1)->get();
        $data['actions'] = Log::distinct()->pluck('action');
        $data['model_types'] = Log::whereNotNull('model_type')->distinct()->pluck('model_type');

        return view($this->admin_view . '.index', $data);
    }

    public function show($id)
    {
        $log = Log::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user ? $log->user->name : 'System',
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'model_type' => $log->model_type ? class_basename($log->model_type) : null,
                'model_id' => $log->model_id,
                // 'old_data' => $this->FormatData($log->old_data),
                // 'new_data' => $this->FormatData($log->new_data),
            ]
        ]);
    }

    private function FormatData($jsonData)
    {
        if (!$jsonData) {
            return null;
        }

        try {
            $data = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return 'Invalid JSON data';
            }

            $html = '<div class="simple-data">';
            foreach ($data as $key => $value) {
                $formattedKey = $this->formatKey($key);
                $formattedValue = $this->formatValue($value);
                $html .= '<div class="data-row">';
                $html .= '<span class="data-key">' . $formattedKey . ':</span>';
                $html .= '<span class="data-value">' . $formattedValue . '</span>';
                $html .= '</div>';
            }
            $html .= '</div>';

            return $html;
        } catch (\Exception $e) {
            return 'Error parsing data';
        }
    }

    private function formatKey($key)
    {
        $keyMap = [
            'id' => trans('logs.id'),
        'invoice_number' => trans('logs.invoice_number'),
        'client_id' => trans('logs.client_id'),
        'subscription_id' => trans('logs.subscription_id'),
        'amount' => trans('logs.amount'),
        'remaining_amount' => trans('logs.remaining_amount'),
        'paid_amount' => trans('logs.paid_amount'),
        'enshaa_date' => trans('logs.enshaa_date'),
        'due_date' => trans('logs.due_date'),
        'last_notified_at' => trans('logs.last_notified_at'),
        'status' => trans('logs.status'),
        'auto_generated' => trans('logs.auto_generated'),
        'paid_date' => trans('logs.paid_date'),
        'notes' => trans('logs.notes'),
        'invoice_type' => trans('logs.invoice_type'),
        'created_by' => trans('logs.created_by'),
        'updated_by' => trans('logs.updated_by'),
        'deleted_at' => trans('logs.deleted_at'),
        'created_at' => trans('logs.created_at'),
        'updated_at' => trans('logs.updated_at'),
        'name' => trans('logs.name'),
        'email' => trans('logs.email'),
        'phone' => trans('logs.phone'),
        'address' => trans('logs.address'),
        'client_type' => trans('logs.client_type'),
        'price' => trans('logs.price'),
        'duration' => trans('logs.duration'),
        'description' => trans('logs.description'),
        ];

        return $keyMap[$key] ?? str_replace('_', ' ', ucfirst($key));
    }

    private function formatValue($value)
    {
        if ($value === null) {
            return '<span class="text-muted">NULL</span>';
        }

        if ($value === '') {
            return '<span class="text-muted">Empty</span>';
        }

        if (is_bool($value)) {
            return $value ?
                '<span class="badge bg-success">Yes</span>' :
                '<span class="badge bg-danger">No</span>';
        }

        if (is_numeric($value)) {
            return '<strong>' . $value . '</strong>';
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return '<span class="text-primary">' . $value . '</span>';
        }

        return $value;
    }

    public function destroy($id)
    {
        try {
            $log = Log::findOrFail($id);
            $log->delete();

            return response()->json([
                'success' => true,
                'message' => trans('logs.delete_success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('logs.delete_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearOldLogs(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $cutoffDate = now()->subDays($days);
            // dd($cutoffDate);
            $deletedCount = Log::where('created_at', '<', $cutoffDate)->delete();

            return response()->json([
                'success' => true,
                'message' => trans('logs.cleared_success', ['count' => $deletedCount])
                // 'message' => $cutoffDate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('logs.clear_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }
}
