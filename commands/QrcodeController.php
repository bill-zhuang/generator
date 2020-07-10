<?php

namespace app\commands;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use yii\console\Controller;

class QrcodeController extends Controller
{
    public function actionGenerate()
    {
        $list = [
            ''
        ];
        foreach ($list as $text) {
            $this->initCode($text);
        }
    }

    protected function initCode($text, $writePath = '')
    {
        $qrCode = new QrCode($text);
        $qrCode->setSize(300);

        // Set advanced options
        $qrCode->setWriterByName('png');
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        //$qrCode->setLabel('Scan the code', 16, __DIR__.'/../assets/fonts/noto_sans.otf', LabelAlignment::CENTER());
        //$qrCode->setLogoPath(__DIR__.'/../assets/images/symfony.png');
        //$qrCode->setLogoSize(75, 75);
        $qrCode->setValidateResult(false);

        // Apply a margin and round block sizes to improve readability
        // Please note that rounding block sizes can result in additional margin
        $qrCode->setRoundBlockSize(true);
        $qrCode->setMargin(10);

        // Set additional writer options (SvgWriter example)
        $qrCode->setWriterOptions(['exclude_xml_declaration' => true]);

        // Directly output the QR code
        //header('Content-Type: '.$qrCode->getContentType());
        //echo $qrCode->writeString();

        // Save it to a file
        if (empty($writePath)) {
            $writePath = __DIR__.'/../runtime/qrcode/qrcode.png';
        }
        $qrCode->writeFile($writePath);

        // Generate a data URI to include image data inline (i.e. inside an <img> tag)
        //$dataUri = $qrCode->writeDataUri();
    }
}