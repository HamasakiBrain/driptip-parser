<?php
require 'vendor/autoload.php';
require 'simple_html_dom.php';

ini_set('display_errors', 0);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$output = [];

/**
 * @param int $page
 * @return false|string
 */
function parse(int $page = 1)
{
    $output = [];
    $url = "https://driptip.ru/zhidkosti/?order=desc&sort=stock&page=$page";
    $html = file_get_html($url);
    $products = $html->find('.product');
    foreach ($products as $product) {

        $name = $product->find('.mobile_name')[0]->plaintext;
        $description = $product->find('.item_descr')[0]->plaintext;
        $image = $product->find('.img_middle_in img')[0]->attr['src'];
        $table = $product->find('.features');
        $price = $product->find('span.price')[0]->plaintext;
        $price = str_replace(" ₽ ", '', $price);
        $params = [];
        foreach ($table as $item) {
            $t = $item->find('tr');
            for ($i = 0; $i < count($t); $i++) {
                $params[$i] .= trim($t[$i]->plaintext);

            }
        }
        array_push($output, [
            'name' => trim($name),
            'description' => trim($description),
            'price' => $price + 100,
            'image' => "https://driptip.ru" . $image,
            'params' => $params
        ]);
    }

    return $output;
}


/**
 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
 */
function saveToCsv(array $records)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->getColumnDimension('A')->setWidth(70);
    $sheet->getColumnDimension('B')->setWidth(60);
    $sheet->getColumnDimension('C')->setWidth(20);
//    $sheet->getColumnDimension('D')->setWidth(40);

    $sheet->setCellValue('A1', 'Название');
    $sheet->setCellValue('B1', 'Описание');
    $sheet->setCellValue('C1', 'Цена');
//    $sheet->setCellValue('D1', 'Картинка');
    for ($i = 0; $i < count($records); $i++){
        $volume = trim(explode("Объем", trim($records[$i]["params"][0]))[1]);
        $nicotine = trim(explode("Крепость", trim($records[$i]["params"][1]))[1]);
        $balance =  trim(explode("Баланс VG/PG", trim($records[$i]["params"][2]))[1]);
        $type =  trim(explode("Тип никотина", trim($records[$i]["params"][3]))[1]);
        $sheet->setCellValue("A" . ($i + 2) ."", trim($records[$i]["name"])." ".$volume. " ". $nicotine . " " . $balance);
        $sheet->setCellValue("B" . ($i + 2) ."", trim($records[$i]["description"]));
        $sheet->setCellValue("C" . ($i + 2) ."", $records[$i]["price"]);
//        $sheet->setCellValue("D" . ($i + 2) ."", $records[$i]["image"]);
    }
    try {
        $writer = new Xlsx($spreadsheet);
        $d = date('d-m-Y');
        $fileName = "/results/жижки-$d.xlsx";
        $writer->save(__DIR__. $fileName);
        echo "ok";

    } catch (PhpOffice\PhpSpreadsheet\Writer\Exception $e) {

    }

}

/**
 * @param int $data
 * @return array
 */
function run(int $data): array
{
    $output = [];
    for ($i = 0; $i < $data; $i++){
        foreach(parse($i) as $item){
            array_push($output, $item);
        }
    }
    return $output;
}


saveToCsv(run(15));

