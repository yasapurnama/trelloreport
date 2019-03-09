<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;

class Trello extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.htmldDebug
	 */
	public function index()
	{
		$data['current_month'] = date('m');
		$data['current_years'] = date('Y');
		$this->load->view('trello_main', $data);
	}

	public function download(){
		$post_data = $this->input->post();
		$this->load->helper('new_helper');
		//dDebug($this->input->post());

		$this->load->library('Pu12Scraper');
		//$this->pu12scraper->test();
		
		if(!empty($post_data['api_key']) && !empty($post_data['token']) && !empty($post_data['board_id']) && !empty($post_data['list_name'])){
			#parsing parameter
			$api_key		= $post_data['api_key'];
			$token			= $post_data['token'];
			$board_id		= $post_data['board_id'];
			$list_name		= $post_data['list_name'];
			$startDate		= $post_data['startDate'];
			$endDate		= $post_data['endDate'];
			$list_id		= 0;
			$card_data		= [];
			$report_data	= [];
		}
		else{
			echo "Missing parameters.";
			exit;
		}
		
		
		/**
		 * API Request
		 */
		#get list data
		$data = $this->pu12scraper->request('GET', 'https://api.trello.com/1/boards/'.$board_id.'/lists?fields=id,name&key='.$api_key.'&token='.$token);
		$list_data = json_decode($data->getContent());
		foreach ($list_data as $key => $list) {
			if($list->name == $list_name){
				$list_id = $list->id;
				break;
			}
		}
		if($list_id > 0){
			#get card data
			$data = $this->pu12scraper->request('GET', 'https://api.trello.com/1/lists/'.$list_id.'/cards?fields=id,name&key='.$api_key.'&token='.$token);
			$card_data = json_decode($data->getContent());

			if(!empty($card_data)){
				#get card checklists
				foreach ($card_data as $key => $card) {
					$time_start		= strtotime(str_replace('/', '-', $startDate));
					$time_end		= strtotime(str_replace('/', '-', $endDate));
					$time_created	= hexdec(substr($card->id,0,8));

					#check datepicker
					if($time_created >= $time_start && $time_created <= $time_end){
						#get card items
						$data = $this->pu12scraper->request('GET', 'https://api.trello.com/1/cards/'.$card->id.'/checklists?fields=id,name&key='.$api_key.'&token='.$token);
						$checklist_data = json_decode($data->getContent());

						if(!empty($checklist_data)){
							foreach ($checklist_data as $data) {
								$report_data[] = [
									"card_id" => $card->id,
									"card_name" => $card->name,
									"card_timestamp" => date('d-m-Y', $time_created),
									"checklist_id" => $data->id,
									"checklist_name" => $data->name,
									"checklist_checkItems" => $data->checkItems
								];	
							}
						}
					}
				}
					
			}
			else{
				echo "Trello API failed get data card list ".$list_name.".";
				exit;
			}	
		}
		else{
			echo "Trello API failed get list_id ".$list_name.".";
			exit;
		}
		

		
		/**
		 * Generate to Excel
		 */
		$spreadsheet 	= new Spreadsheet();

        #advanced binder
        Cell::setValueBinder(new AdvancedValueBinder());

        #Set document properties
        $spreadsheet->getProperties()->setCreator('Admin')
            ->setLastModifiedBy('Admin')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');

        $spreadsheet->getActiveSheet()->setTitle('TRELLO REPORT '.strtoupper($list_name));

        #Width
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(6);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(36);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(14);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(12);

        #Title
        $spreadsheet->getActiveSheet()
            ->setCellValue('A2', 'TRELLO REPORT '.strtoupper($list_name))
            ->getStyle('A2')
            ->applyFromArray([ 'font' => [ 'size' => 16, 'bold' => true ] ]);

        $spreadsheet->getActiveSheet()
            ->setCellValue('A4', 'Generated : '.date('r'));

        #Table
        $spreadsheet->getActiveSheet()
            ->getStyle("A6:G6")
            ->applyFromArray([ 'font' => [ 'bold' => true ] ]) //text bold
            ->applyFromArray([ 'alignment' => [ 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER ] ]) //text align center
            ->applyFromArray([ 'fill' => [ 'fillType' => Fill::FILL_SOLID, 'startColor' => [ 'argb' => 'FFC0C0C0' ] ] ]);

        $spreadsheet->getActiveSheet()
            ->setCellValue('A6', 'NO')
            ->setCellValue('B6', 'CARD NAME')
            ->setCellValue('C6', 'DATE')
            ->setCellValue('D6', 'ACTIVITY')
            ->setCellValue('E6', 'INFO')
            ->setCellValue('F6', 'TASK ID')
            ->setCellValue('G6', 'ESTIMATE');

        $card_arr	= [];
        $no 		= 7;
        $num 		= 1;
        foreach ($report_data as $key => $report) {
        	$checklist_arr = [];
        	foreach ($report['checklist_checkItems'] as $key => $item) {
        		if(!in_array($report['card_id'], $card_arr)){
        			$spreadsheet->getActiveSheet()
		                ->setCellValue('A'.$no, $num)
		                ->setCellValue('B'.$no, $report['card_name'])
		                ->setCellValue('C'.$no, $report['card_timestamp']);
	                $card_arr[] = $report['card_id'];
	                $num++;
        		}
        		if(!in_array($report['checklist_name'], $checklist_arr)){
        			$spreadsheet->getActiveSheet()
	            	    ->setCellValue('D'.$no, $report['checklist_name']);
	                $checklist_arr[] = $report['checklist_name'];
        		}
        		$parse_item = parseEstimate($item->name);
        		$spreadsheet->getActiveSheet()
	                ->setCellValue('E'.$no, $item->name)
	                ->setCellValue('F'.$no, $parse_item['task_id'])
	                ->setCellValue('G'.$no, $parse_item['estimate'])
	                ->getStyle('A'.$no.":G".$no)
	                ->applyFromArray([ 'alignment' => [ 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER ] ]);
	            $spreadsheet->getActiveSheet()
	        	    ->getStyle('E'.$no)
	        	    ->getAlignment()->setWrapText(true);
                $no++;
        	}
        }

        $spreadsheet->getActiveSheet()
        	->mergeCells('E'.$no.':F'.$no)
            ->setCellValue('E'.$no, 'TOTAL')
            ->setCellValue('G'.$no, '=SUM(G7:G'.($no-1).')')
            ->getStyle('E'.$no.":G".$no)
            ->applyFromArray([ 'font' => [ 'bold' => true ] ])
            ->applyFromArray([ 'alignment' => [ 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER ] ]);

        $spreadsheet->getActiveSheet()
            ->getStyle("A6:G".$no)
            ->applyFromArray([ 'borders' => [ 'allBorders' => [ 'borderStyle' => Border::BORDER_THIN ] ] ]);

        //testing for mac
        if (ob_get_contents()) ob_end_clean();
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        //$spreadsheet->setActiveSheetIndex(3);

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="trello-report-'.strtolower(str_replace(' ', '-', $list_name)).'-' . date('d-m-Y') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;

	}
}
