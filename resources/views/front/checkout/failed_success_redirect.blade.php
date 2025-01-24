<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeGuruKul</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>
    {{-- <div class="spinner-border text-secondary" role="status">
        <span class="sr-only"></span>
    </div> --}}
    @php
        $url = '';

        if (request()->payment_status == 1) {
            if (request()->advisement == 1) {
                $url = route('send_razorpay_paymentLink', [
                    'payment_status' => 1,
                    'courseId' => request()->courseId,
                    'orderId' => request()->orderId,
                    'advisement'=>request()->advisement == 1,
                    'isCombineCourse' => request()->isCombineCourse,
                ]);
            } else {
                $url = route('send_razorpay_paymentLink', [
                    'payment_status' => 1,
                    'courseId' => request()->courseId,
                    'orderId' => request()->orderId,
                    'isCombineCourse' => request()->isCombineCourse,
                ]);
            }
        } else {
            $url = route('send_razorpay_paymentLink', [
                'payment_status' => 0,
                'courseId' => '',
                'orderId' => '',
                'advisement'=>request()->advisement == 1,
                    'isCombineCourse' => request()->isCombineCourse,
            ]);
        }

        $originalUrl = $url;
        $newUrl = str_replace('https://', 'lg://', $originalUrl);
    @endphp

    <a id="paymentLink" href="{{ $newUrl }}" class="auto_trigger paymentLink""></a>

    <script>
        $(document).ready(function() {
            $('#paymentLink')[0].click();
        });



        $(document).ajaxStart(function() {
            $('.spinner-border').show();
        }).ajaxStop(function() {
            $('.spinner-border').hide();
        })
    </script>

</body>

</html>
