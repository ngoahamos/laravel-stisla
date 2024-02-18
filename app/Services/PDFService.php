<?php

namespace App\Services;
use \PDF;

use Illuminate\Http\Response;

class PDFService
{
    public function generatePDF(array $data, string $view,  $size = 'a4',  $orientation = 'portrait') {
        try {
            $pdf =  PDF::loadView($view,$data)->setPaper($size)->setOrientation($orientation);


            if (safe_indexing($data, 'options'))
            {
                $pdf = $pdf->setOption("footer-right", "Page [page] of [topage]");
            }
            return prepareResponse(true, $pdf);
        } catch (\Exception $error) {

            return prepareResponse(false, ['message' => $error->getMessage()], Response::HTTP_EXPECTATION_FAILED);
        }

    }
}
