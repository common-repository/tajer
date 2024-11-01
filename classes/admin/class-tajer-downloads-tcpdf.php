<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class Tajer_Downloads_Tcpdf extends Tajer_Sales_Tcpdf {

	public function __construct( $orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $from, $to ) {
		parent::__construct( $orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $from, $to );
	}

	//Page header
	public function Header() {
		$current_user = wp_get_current_user();
		// Set font
		$this->SetFont( 'helvetica', 'B', 20 );
		// Title
		$this->Cell( 0, 15, __( 'Downloads Report' ), 0, false, 'C', 0, '', 0, false, 'M', 'M' );
		$this->CreateTextBox( __( 'Report Creation Date: ', 'tajer' ) . date( 'Y-m-d' ), 0, 10, 0, 10, 10, '', 'L' );
		$this->CreateTextBox( __( 'Created By: ', 'tajer' ) . $current_user->user_firstname . ' ' . $current_user->user_lastname, 0, 15, 0, 10, 10, '', 'L' );
		$this->CreateTextBox( __( 'For Period From: ', 'tajer' ) . $this->from, 0, 10, 0, 10, 10, '', 'R' );
		$this->CreateTextBox( __( 'To: ', 'tajer' ) . $this->to, 0, 15, 0, 10, 10, '', 'R' );
		$this->Line( 20, 25, 195, 25 );
	}


// Load table data from file
	public function LoadData( $items ) {
// Read file lines

		$data = array();
		foreach ( $items as $item ) {
//			$data[] = explode( ';', chop( $line ) );
			$data[] = array(
				$item['user_product'],
				$item['date'],
				$item['product_id'],
				$item['product_sub_id'],
				$item['file_id'],
				$item['ip']
			);
		}

		return $data;
	}

	// Colored table
	public function ColoredTable( $header, $data ) {
// Colors, line width and bold font
		$this->SetFillColor( 255, 0, 0 );
		$this->SetTextColor( 255 );
		$this->SetDrawColor( 128, 0, 0 );
		$this->SetLineWidth( 0.3 );
		$this->SetFont( '', 'B' );
// Header
		$w           = array( 30, 45, 25, 35, 15, 35 );
		$num_headers = count( $header );
		for ( $i = 0; $i < $num_headers; ++ $i ) {
			$this->Cell( $w[ $i ], 7, $header[ $i ], 1, 0, 'C', 1 );
		}
		$this->Ln();
// Color and font restoration
		$this->SetFillColor( 224, 235, 255 );
		$this->SetTextColor( 0 );
		$this->SetFont( '' );
// Data
		$fill = 0;
		foreach ( $data as $row ) {
			$this->Cell( $w[0], 6, $row[0], 'LR', 0, 'L', $fill );
			$this->Cell( $w[1], 6, $row[1], 'LR', 0, 'L', $fill );
			$this->Cell( $w[2], 6, $row[2], 'LR', 0, 'R', $fill );
			$this->Cell( $w[3], 6, $row[3], 'LR', 0, 'R', $fill );
			$this->Cell( $w[4], 6, $row[4], 'LR', 0, 'R', $fill );
			$this->Cell( $w[5], 6, $row[5], 'LR', 0, 'R', $fill );
			$this->Ln();
			$fill = ! $fill;
		}
		$this->Cell( array_sum( $w ), 0, '', 'T' );
	}
}