<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;

use App\Invoice;
use App\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends BaseController
{
    public $status = 200;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";

    public function __construct()
    {
        $this->middleware('checkPermission', ['only' => ['pay', 'received']]);
        // $this->middleware('auth');
    }

    public function pay(Request $request)
    {
        $this->_setPageTitle('Payments');
        $data = [
            'content' => 'List of payments for book appointment.'
        ];

        if ($request->ajax()) {
            try {
                $query = new Payment();
                $query = $query->where(['user_id' => Auth::id(), 'status' => 'paid'])->orderBy('id', 'DESC');

                $datatable = DataTables::of($query->get());

                $datatable = $datatable->addColumn('appointment_with', function ($q) {
                    if($q->appointment->doctor_id){
                        return isset($q->appointment->doctor->name) ? $q->appointment->doctor->name : '';
                    }else{
                        return isset($q->appointment->diagnostics->name) ? $q->appointment->diagnostics->name : '';
                    }
                });

                $datatable = $datatable->addColumn('refund_amount', function ($q) {
                    return isset($q->refund_amount) ? $q->refund_amount : 0;
                });

                $datatable = $datatable->addColumn('action', function ($q) {

                    $button = '<a href="javascript:;" class="f-16 ml-1 mr-1" data-toggle="tooltip" data-placement="top" data-trigger="hover" title="View Payment" onclick="viewPayment(' . $q->id . ')"><i class="far fa-eye"></i></a>';
                    $button .= '<a href="' . route('payment.invoice.download', [$q->id]) . '" class="f-16 ml-1 mr-1" data-toggle="tooltip" data-placement="top" data-trigger="hover" title="Download Invoice"><i class="fas fa-receipt"></i></a>';

                    return $button;
                });

                $datatable = $datatable->addColumn('txn_date', function ($q) {
                    $date =  Carbon::createFromFormat('Y-m-d H:i:s', $q->txn_date)->setTimezone('Asia/Kolkata');
                    return Carbon::parse($date)->isoFormat('Do MMMM YYYY, h:mm a');
                });

                $datatable = $datatable->rawColumns(['name', 'email', 'phone', 'locality', 'address', 'action']);

                $datatable = $datatable->make(true);
            } catch (Exception $e) {
                $datatable = Datatables::of(Payment::select()->take(0)->get());
                $datatable = $datatable->make(true);
            }
            return $datatable;
        }

        return view('front.payments.pay')->with($data);
    }

    public function received(Request $request)
    {
        $this->_setPageTitle('Payments');
        $data = [
            'content' => 'List of payments received',
        ];

        if ($request->ajax()) {
            try {
                $query = new Payment();
                if (checkPermission(['doctor', 'clinic', 'hospital','diagnostics'])) {
                    $practice_ids = Auth::user()->practiceAsStaff->pluck('id')->toArray(); //use for get all auth user practice ids
                }
                if (checkPermission(['manager', 'accountant'])) {
                    $practice_ids = Auth::user()->addedBy->practiceAsStaff->pluck('id')->toArray(); //use for get all auth user practice ids
                }
                $query = $query->whereHas('appointment', function ($q) use ($practice_ids) {
                    $q->whereIn('practice_id', $practice_ids);
                })->orderBy('id', 'DESC');
                $datatable = DataTables::of($query->get());

                $datatable = $datatable->addColumn('patient', function ($q) {
                    $btn = '<span class="d-inline-flex"><img src=" ' . $q->user->profile_picture . '" class="rounded rounded-circle float-left mr-2" style="width: 55px;min-height: 55px;"/> <span><h6 class="mb-0">' . $q->appointment->patient_name . '</h6><p class="mb-0 l-0 f-12">' . $q->appointment->patient_phone . '</p><p class="mb-0 l-0 f-12">' . $q->appointment->patient_email . '</p></span></span>';
                    return $btn;
                });

                $datatable = $datatable->addColumn('refund_amount', function ($q) {
                    return isset($q->refund_amount) ? $q->refund_amount : 0;
                });
                $datatable = $datatable->addColumn('txn_date', function ($q) {
                    $date =  Carbon::createFromFormat('Y-m-d H:i:s', $q->txn_date)->setTimezone('Asia/Kolkata');
                    return Carbon::parse($date)->isoFormat('Do MMMM YYYY, h:mm a');
                });

                $datatable = $datatable->addColumn('action', function ($q) {

                    $button = '<a href="javascript:;" class="f-16 ml-1 mr-1" data-toggle="tooltip" data-placement="top" data-trigger="hover" title="View Payment" onclick="viewPayment(' . $q->id . ')"><i class="far fa-eye"></i></a>';
                    $button .= '<a href="' . route('payment.invoice.download', [$q->id]) . '" class="f-16 ml-1 mr-1" data-toggle="tooltip" data-placement="top" data-trigger="hover" title="Download Invoice"><i class="fas fa-receipt"></i></a>';

                    return $button;
                });

                $datatable = $datatable->rawColumns(['patient', 'refund_amount', 'action']);

                $datatable = $datatable->make(true);
            } catch (Exception $e) {
                $datatable = Datatables::of(Payment::select()->take(0)->get());
                $datatable = $datatable->make(true);
            }
            return $datatable;
        }

        return view('front.payments.received')->with($data);
    }

    public function show($id)
    {
        /* try { */
        $data['title'] = 'View Payment Detail';
        $data['payment'] = Payment::find($id);
        $html = view('front.payments.view', $data)->render();
        $result = ['status' => $this->success, 'message' => '', 'html' => $html];
        /* } catch (Exception $e) {
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        } */
        return Response::json($result, $this->status);
    }

    public function invoiceDownload($id, Invoice $invoice)
    {
        $payment = Payment::find($id);
        if (!empty($payment) && !empty($payment->invoice_id)) {
            $invoice_filename = 'invoice_' . $payment->invoice_id . '.pdf';
            // File::exists($myfile);
            $invoice_filepath = storage_path('app/invoice/' . $invoice_filename);
            if (File::exists($invoice_filepath)) {
            } else {
                // p($payment->appointment);
                if (!empty($payment->appointment)) {
                    $price = $payment->amount - ($payment->amount * 0.18);
                    $gst = $payment->amount  - $price;
                    $data = [
                        'appointment' => $payment->appointment,
                        'price' => $price,
                        'gst' => $gst
                    ];
                    $output = $invoice->generate('front.invoice.book_appointment', $data);
                }
                Storage::put('invoice/' . $invoice_filename, $output);
                $invoice_filepath = storage_path('app/invoice/' . $invoice_filename);
            }
            $headers = ['Content-Type: application/pdf'];

            return Response::download($invoice_filepath, $invoice_filename, $headers);
        }
    }
}
