<?php

namespace App\Http\Controllers;

use App\Libraries\Helper;
use App\Libraries\InvoiceNotifyMode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateTime;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    const EPAYMENT_CREATE_URL = "api/v2/web-ven-sdd/epayment/create/";
    const EPAYMENT_STATUS_URL = "api/v2/web-ven-sdd/epayment/status/";

    public $url;
    private $invoiceCreateUrl;
    private $invoiceStatusUrl;
    private $apiKey, $vendorId, $branchId, $terminalId;

    public $mode;
    public $msisdn;
    public $email;
    public $customerName;
    public $amount;
    public $date;
    public $successUrl;
    public $errorUrl;
    public $externalReference;
    public $description;

    public function __construct()
    {

        $this->url = config('sadad.url.test');
        $this->apiKey = config('sadad.apiKey');
        $this->invoiceCreateUrl = $this->url . Self::EPAYMENT_CREATE_URL;
        $this->invoiceStatusUrl = $this->url . Self::EPAYMENT_STATUS_URL;
        $this->branchId = config('sadad.branchId');
        $this->vendorId = config('sadad.vendorId');
        $this->terminalId = config('sadad.terminalId');
    }

    public function CreateSmsRequest()
    {
        $response = new \stdClass();
        $errors = $this->ValidateRequestParameters(InvoiceNotifyMode::SMS);
        if (count($errors) == 0) {
            $invoice = $this->setInvoiceObject(InvoiceNotifyMode::SMS);
            $response = Helper::post($this->invoiceCreateUrl, json_encode($invoice));
        } else {
            $response->{'error-message'} = implode('|', $errors);
            $response->{'error-code'} = 3;
        }
        return json_encode($response);
    }

    /**
     * Create request using email as notification mode.
     *
     * @return    object
     *
     */
    public function CreateEmailRequest()
    {
        $response = new \stdClass();
        $errors = $this->ValidateRequestParameters(InvoiceNotifyMode::EMAIL);
        if (count($errors) == 0) {
            $invoice = $this->setInvoiceObject(InvoiceNotifyMode::EMAIL);
            $response = Helper::post($this->invoiceCreateUrl, json_encode($invoice));
        } else {
            $response->{'error-message'} = implode('|', $errors);
            $response->{'error-code'} = 3;
        }
        return json_encode($response);
    }

    /**
     * Create invoice request that will generate url of payment.
     *
     * @return    object
     *
     */
    public function CreateLinkRequest(Request $request)
    {

        if ($request->isMethod('post')) {

            $rules = [
                'customerName' => 'required',
                'amount' => 'required',
                'externalReference' => 'required',
                'description' => 'required',
                'msisdn' => 'required',
                'email' => 'required|email',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {

                return view('sadad.invoice.index', [
                    'errors' => $validator->errors()
                ]);
            }
                $customerName = $request->get('customerName');
                $amount = $request->get('amount');
                $date = $request->has('date') ? $request->get('date') : Carbon::now();
                $externalReference = $request->get('externalReference');
                $description = $request->get('description');
                $msisdn = $request->get('msisdn');
                $email = $request->get('email');

                $invoice = [];
                $invoice['api-key'] = $this->apiKey;
                $invoice['vendor-id'] = $this->vendorId;
                $invoice['branch-id'] = $this->branchId;
                $invoice['terminal-id'] = $this->terminalId;
                $invoice['customer-name'] = $customerName;
                $invoice['amount'] = $amount;
                $invoice['date'] = $date;
                $invoice['notification-mode'] = InvoiceNotifyMode::ONLINE;
                $invoice['external-reference'] = $externalReference;
                $invoice['description'] = $description;
                $invoice['msisdn'] = $msisdn;
                $invoice['email'] = $email;
                $invoice['success-url'] = config('sadad.successUrl');
                $invoice['error-url'] = config('sadad.errorUrl');

                $response = Helper::post($this->invoiceCreateUrl, json_encode($invoice));

                $payment_url = $response['payment-url'];

                return Redirect::away($payment_url);
        } else {
            return view('sadad.invoice.index');
        }
    }

    public function successProcess(Request $request)
    {
        app('log')->error($request->all());
        return $this->SendInvoiceStatusRequest($request['TransactionIdentifier']);

    }
    public function errorProcess(Request $request)
    {
        app('log')->error($request->all());
        dd($request->all());
    }

    /**
     * Sends invoice status against transaction reference passed to it that can be captured from creation response.
     *
     * @param string $transactionReference The transaction reference for which we are going to query the status of invoice
     * @return    object
     *
     */
    public function SendInvoiceStatusRequest($transactionReference)
    {
        $invoiceStatusRequest = [];
        $invoiceStatusRequest['api-key'] = $this->apiKey;
        $invoiceStatusRequest['vendor-id'] = $this->vendorId;
        $invoiceStatusRequest['branch-id'] = $this->branchId;
        $invoiceStatusRequest['terminal-id'] = $this->terminalId;
        $invoiceStatusRequest['transaction-reference'] = $transactionReference;
        return json_encode(Helper::post($this->invoiceStatusUrl, json_encode($invoiceStatusRequest)));
    }

    /**
     * Set invoice object that is going to be generated.
     *
     * @param string $mode The notification mode which will be used to generate the invoice
     * @return    object
     *
     */
    private function setInvoiceObject($mode, $invoice)
    {
        $invoice = [];
        $invoice['api-key'] = $this->apiKey;
        $invoice['vendor-id'] = $this->vendorId;
        $invoice['branch-id'] = $this->branchId;
        $invoice['terminal-id'] = $this->terminalId;
        $invoice['customer-name'] = $this->customerName;
        $invoice['amount'] = $this->amount;
        $invoice['date'] = $this->date;
        $invoice['notification-mode'] = $mode;
        $invoice['external-reference'] = $this->externalReference;
        $invoice['description'] = $this->description;
        if ($mode == InvoiceNotifyMode::SMS) {
            $invoice['email'] = null;
            $invoice['msisdn'] = $this->msisdn;
        }
        if ($mode == InvoiceNotifyMode::EMAIL) {
            $invoice['email'] = $this->email;
            $invoice['msisdn'] = null;
        }
        if ($mode == InvoiceNotifyMode::ONLINE) {
            $invoice['msisdn'] = $this->msisdn;
            $invoice['email'] = $this->email;
            $invoice['success-url'] = $this->successUrl;
            $invoice['error-url'] = $this->errorUrl;
        }
        return $invoice;
    }

    /**
     * Validates request parameters according to the notification mode passed to it.
     *
     * @param string $mode The notification mode which will be used to generate the invoice
     * @return    array
     *
     */
    private function ValidateRequestParameters($mode)
    {
        $errors = [];
        if ($this->amount <= 0) {
            array_push($errors, "Amount must be greater than zero");
        }

        if (!$this->date || $this->date == (new DateTime())->setTimestamp(0)) {
            array_push($errors, "Date is missing");
        }

        if (empty($this->email) && $mode == InvoiceNotifyMode::EMAIL) {
            array_push($errors, "Email is missing");
        }

        if (!Helper::isValidEmail($this->email) && $mode == InvoiceNotifyMode::EMAIL) {
            array_push($errors, "Email address is invalid");
        }

        if (empty($this->msisdn) && $mode == InvoiceNotifyMode::SMS) {
            array_push($errors, "Msisdn is missing");
        }

        if (!empty($this->msisdn) && (strlen($this->msisdn) != 11 || !Helper::startsWith($this->msisdn, "973"))) {
            array_push($errors, "Msisdn must be of 11 digits, starting with 973");
        }

        if ($mode == InvoiceNotifyMode::ONLINE && empty($this->msisdn) && empty($this->email)) {
            array_push($errors, "Msisdn or Email is missing");
        }

        if (empty($this->customerName)) {
            array_push($errors, "CustomerName is missing");
        }

        if ($mode == InvoiceNotifyMode::ONLINE) {
            if (empty($this->successUrl)) {
                array_push($errors, "SuccessUrl is missing");
            }

            if (empty($this->errorUrl)) {
                array_push($errors, "ErrorUrl is missing");
            }

        }
        return $errors;
    }
}
