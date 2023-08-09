<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            /* font-family: nepali_english_font, sans-serif; */
            font-family: DejaVu Sans, sans-serif, Preeti;
            line-height: 1.6;
            margin: 40px;
        }

        h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }

        p {
            font-size: 14px;
            text-align: justify;
        }

        .page-break {
            page-break-after: always;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            text-align: center;
            color: #888;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>{{$data['document_name']}}</h1>
    <p>
        {!! nl2br($data['ocr_text']) !!}
    </p>

    <!-- Add a page break if necessary -->
    <!-- <div class="page-break"></div> -->

</body>
</html>
