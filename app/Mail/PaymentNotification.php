<?php

namespace App\Mail;

use PDF as MPDF;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\File;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PaymentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $subject;
    public $invoiceData;
    public $userCourseId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content, $subject, $invoiceData, $userCourseId = null, $device_type=null)
    {

        $this->content = $content;
        $this->subject = $subject;
        $this->invoiceData = $invoiceData;
        $this->userCourseId = $userCourseId;
        $this->device_type = $device_type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // dd($this->invoiceData);
        $view = $this->view('admin.mails.payment');
        if (!empty($this->subject)) {
            $view->subject($this->subject);
        }
        if (empty($this->invoiceData)) {

            $this->invoiceData = collect([]);
        } else {
            if ($this->device_type !=2) {
                $invoiceData = get_invoice_data($this->invoiceData->id);

                if ($invoiceData->after_deduction_price !== "0") {
                    // $pdfPath = storage_path('app/public/invoice');

                    $pdfPath = storage_path('app/public/invoice');
                    if (!File::exists($pdfPath)) {
                        File::makeDirectory($pdfPath, 0777, true); // Create with 0777 permissions
                    }

                    $fileName = 'invoice_' . $this->invoiceData->id . '.pdf';

                    if (file_exists($pdfPath . '/' . $fileName)) {
                        $this->attach($pdfPath . '/' . $fileName);
                    } else {
                        ini_set('max_execution_time', 180);
                        // Storage::disk('local')->makeDirectory('/invoice');

                        // No need to call get_invoice_data again, reuse the variable

                        $pdf = MPDF::loadView('front.student.view_invoice_device', compact('invoiceData'));
                        $pdf->save($pdfPath . '/' . $fileName);
                        $this->attach($pdfPath . '/' . $fileName);
                    }
                }
            }
        }

        return $view->with(['content' => $this->invoiceData,"device_type"=>$this->device_type]);
    }
}
