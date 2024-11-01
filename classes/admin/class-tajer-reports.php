<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class Tajer_Reports {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'init', array( $this, 'generateFile' ) );
//		add_action( 'wp_ajax_tajer_save_reports', array( $this, 'tajer_save_reports' ) );
		add_action( 'wp_ajax_tajer_get_graph_data', array( $this, 'get_graph_data' ) );

	}

	function generateFile() {
		if ( isset( $_REQUEST['tajer-generate-file'] ) ) {
			$nonce = $_REQUEST['tajer_reports_nonce_field'];
			if ( ! wp_verify_nonce( $nonce, 'tajer_reports_nonce' ) ) {
				wp_die( 'Security check' );
			}

			$for  = sanitize_text_field( $_REQUEST['tajer-report-for'] );
			$from = sanitize_text_field( $_REQUEST['tajer-export-from'] );
			$to   = sanitize_text_field( $_REQUEST['tajer-export-to'] );

			switch ( $for ) {
				case 'sales':
					$items = Tajer_DB::get_items_statistics_by_buying_date( $from, $to, ARRAY_A );
					switch ( sanitize_text_field( $_REQUEST['tajer-fileType'] ) ) {
						case 'csv':
							$this->generate_sales_csv_file( $items );
							break;
						case 'pdf':
							$this->generate_sales_pdf_file( $items, $from, $to );
							break;
					}
					break;
				case 'downloads':
					$items = Tajer_DB::get_downloads_items_by_date( $from, $to, ARRAY_A );
					switch ( sanitize_text_field( $_REQUEST['tajer-fileType'] ) ) {
						case 'csv':
							$this->generate_downloads_csv_file( $items );
							break;
						case 'pdf':
							$this->generate_downloads_pdf_file( $items, $from, $to );
							break;
					}
					break;
				default:
					do_action( 'tajer_generate_report_file' );
					break;
			}
		}
	}

	function generate_sales_pdf_file( $items, $from, $to ) {
// create new PDF document
		$pdf = new Tajer_Sales_Tcpdf( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, $from, $to );

// set document information
		$pdf->SetCreator( apply_filters( 'tajer_admin_reports_pdf_file_set_creator', PDF_CREATOR, $items, $from, $to ) );
		$pdf->SetAuthor( apply_filters( 'tajer_admin_reports_pdf_file_set_author', 'Tajer', $items, $from, $to ) );
		$pdf->SetTitle( apply_filters( 'tajer_admin_reports_pdf_file_set_title', 'Tajer Report', $items, $from, $to ) );
		$pdf->SetSubject( apply_filters( 'tajer_admin_reports_pdf_file_set_subject', 'Tajer Statistics Report', $items, $from, $to ) );
		$pdf->SetKeywords( apply_filters( 'tajer_admin_reports_pdf_file_set_keywords', 'Report, Statistics, Analytics', $items, $from, $to ) );

// set default monospaced font
		$pdf->SetDefaultMonospacedFont( apply_filters( 'tajer_admin_reports_pdf_file_font_monospaced', PDF_FONT_MONOSPACED, $items, $from, $to ) );

// set margins
		$pdf->SetMargins( apply_filters( 'tajer_admin_reports_pdf_file_margin_left', PDF_MARGIN_LEFT, $items, $from, $to ), apply_filters( 'tajer_admin_reports_pdf_file_margin_top', PDF_MARGIN_TOP, $items, $from, $to ), apply_filters( 'tajer_admin_reports_pdf_file_margin_right', PDF_MARGIN_RIGHT, $items, $from, $to ) );
		$pdf->SetHeaderMargin( apply_filters( 'tajer_admin_reports_pdf_file_margin_header', PDF_MARGIN_HEADER, $items, $from, $to ) );
		$pdf->SetFooterMargin( apply_filters( 'tajer_admin_reports_pdf_file_margin_footer', PDF_MARGIN_FOOTER, $items, $from, $to ) );

// set auto page breaks
		$pdf->SetAutoPageBreak( true, apply_filters( 'tajer_admin_reports_pdf_file_set_auto_page_break', PDF_MARGIN_BOTTOM, $items, $from, $to ) );

// set image scale factor
		$pdf->setImageScale( apply_filters( 'tajer_admin_reports_pdf_file_set_image_scale', PDF_IMAGE_SCALE_RATIO, $items, $from, $to ) );

// ---------------------------------------------------------

// set font
		$pdf->SetFont( apply_filters( 'tajer_admin_reports_pdf_file_set_font_family', 'helvetica', $items, $from, $to ), apply_filters( 'tajer_admin_reports_pdf_file_set_font_style', '', $items, $from, $to ), apply_filters( 'tajer_admin_reports_pdf_file_set_font_size', 12, $items, $from, $to ) );

// add a page
		$pdf->AddPage();

// column titles
		$header = apply_filters( 'tajer_admin_reports_pdf_file_set_table_header', array(
			'User ID',
			'Buying Date',
			'Product ID',
			'Product Sub ID',
			'Quantity',
			'Earnings'
		), $items, $from, $to );

// data loading
//		$data = $pdf->LoadData( Tajer_DIR . 'lib/tcpdf/examples/data/table_data_demo.txt' );
		$data = $pdf->LoadData( apply_filters( 'tajer_admin_reports_pdf_file_table_items', $items, $items, $from, $to ) );

// print colored table
		$pdf->ColoredTable( $header, $data );
//		$pdf->SetXY( 10 , 10 );
		$pdf->Cell( 0, 2, apply_filters( 'tajer_admin_reports_pdf_file_format_total_earnings', __( 'Total Earnings: $', 'tajer' ) . number_format( $this->totalEarnings( $items ) ), $items, $from, $to ), 0, 2, 'R' );
		$pdf->Cell( 0, 2, apply_filters( 'tajer_admin_reports_pdf_file_format_total_sales', __( 'Total Sales: ', 'tajer' ) . number_format( $this->totalSales( $items ) ), $items, $from, $to ), 0, 2, 'R' );

// ---------------------------------------------------------

// close and output PDF document
		$pdf->Output( apply_filters( 'tajer_admin_reports_pdf_file_name', 'statistics', $items, $from, $to ) . '.pdf', 'D' );

	}

	function generate_downloads_pdf_file( $items, $from, $to ) {
// create new PDF document
		$pdf = new Tajer_Downloads_Tcpdf( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, $from, $to );

// set document information
		$pdf->SetCreator( apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_creator', PDF_CREATOR, $items, $from, $to ) );
		$pdf->SetAuthor( apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_author', 'Tajer', $items, $from, $to ) );
		$pdf->SetTitle( apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_title', 'Tajer Report', $items, $from, $to ) );
		$pdf->SetSubject( apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_subject', 'Tajer Downloads Report', $items, $from, $to ) );
		$pdf->SetKeywords( apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_keywords', 'Report, Statistics, Analytics, Downloads', $items, $from, $to ) );

// set default monospaced font
		$pdf->SetDefaultMonospacedFont( apply_filters( 'tajer_admin_reports_downloads_pdf_file_font_monospaced', PDF_FONT_MONOSPACED, $items, $from, $to ) );

// set margins
		$pdf->SetMargins( apply_filters( 'tajer_admin_reports_downloads_pdf_file_margin_left', PDF_MARGIN_LEFT, $items, $from, $to ), apply_filters( 'tajer_admin_reports_downloads_pdf_file_margin_top', PDF_MARGIN_TOP, $items, $from, $to ), apply_filters( 'tajer_admin_reports_downloads_pdf_file_margin_right', PDF_MARGIN_RIGHT, $items, $from, $to ) );
		$pdf->SetHeaderMargin( apply_filters( 'tajer_admin_reports_downloads_pdf_file_margin_header', PDF_MARGIN_HEADER, $items, $from, $to ) );
		$pdf->SetFooterMargin( apply_filters( 'tajer_admin_reports_downloads_pdf_file_margin_footer', PDF_MARGIN_FOOTER, $items, $from, $to ) );

// set auto page breaks
		$pdf->SetAutoPageBreak( true, apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_auto_page_break', PDF_MARGIN_BOTTOM, $items, $from, $to ) );

// set image scale factor
		$pdf->setImageScale( apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_image_scale', PDF_IMAGE_SCALE_RATIO, $items, $from, $to ) );

// ---------------------------------------------------------

// set font
		$pdf->SetFont( apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_font_family', 'helvetica', $items, $from, $to ), apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_font_style', '', $items, $from, $to ), apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_font_size', 12, $items, $from, $to ) );

// add a page
		$pdf->AddPage();

// column titles
		$header = apply_filters( 'tajer_admin_reports_downloads_pdf_file_set_table_header', array(
			'User Product',
			'Date',
			'Product ID',
			'Product Sub ID',
			'File ID',
			'IP'
		), $items, $from, $to );

// data loading
//		$data = $pdf->LoadData( Tajer_DIR . 'lib/tcpdf/examples/data/table_data_demo.txt' );
		$data = $pdf->LoadData( apply_filters( 'tajer_admin_reports_downloads_pdf_file_table_items', $items, $items, $from, $to ) );

// print colored table
		$pdf->ColoredTable( $header, $data );
//		$pdf->SetXY( 10 , 10 );
		$pdf->Cell( 0, 2, apply_filters( 'tajer_admin_reports_downloads_pdf_file_format_total_sales', __( 'Total Downloads: ', 'tajer' ) . number_format( $this->totalDownloads( $items ) ), $items, $from, $to ), 0, 2, 'R' );

// ---------------------------------------------------------

// close and output PDF document
		$pdf->Output( apply_filters( 'tajer_admin_reports_downloads_pdf_file_name', 'downloads', $items, $from, $to ) . '.pdf', 'D' );

	}

	function totalEarnings( $items ) {
		$total = 0;
		foreach ( $items as $item ) {
			$total += $item['earnings'];
		}

		return apply_filters( 'tajer_admin_reports_pdf_file_total_earnings', $total, $items );
	}

	function totalSales( $items ) {
		$total = 0;
		foreach ( $items as $item ) {
			$total += $item['quantity'];
		}

		return apply_filters( 'tajer_admin_reports_pdf_file_total_sales', $total, $items );
	}

	function totalDownloads( $items ) {
		$total = 0;
		foreach ( $items as $item ) {
			$total += 1;
		}

		return apply_filters( 'tajer_admin_reports_pdf_file_total_downloads', $total, $items );
	}

	function generate_sales_csv_file( $items ) {

		$items = apply_filters( 'tajer_admin_reports_csv_file_items', $items );

		if ( ! empty( $items ) || ! is_null( $items ) ) {
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment;filename=' . apply_filters( 'tajer_admin_reports_csv_file_name', 'statistics', $items ) . '.csv' );
			header( 'Cache-Control: no-cache, no-store, must-revalidate' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			$csvOutput = fopen( 'php://output', 'w' );
			$headers   = apply_filters( 'tajer_admin_reports_csv_file_headers', array(
				'id',
				'user_id',
				'buying_date',
				'product_id',
				'product_sub_id',
				'author_id',
				'earnings',
				'status',
				'quantity'
			), $items );
			fputcsv( $csvOutput, $headers );
			foreach ( $items as $item ) {
				fputcsv( $csvOutput, $item );
			}
			fclose( $csvOutput );
			exit;
		}
	}

	function generate_downloads_csv_file( $items ) {

		$items = apply_filters( 'tajer_admin_reports_downloads_csv_file_items', $items );

		if ( ! empty( $items ) || ! is_null( $items ) ) {
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment;filename=' . apply_filters( 'tajer_admin_reports_downloads_csv_file_name', 'downloads', $items ) . '.csv' );
			header( 'Cache-Control: no-cache, no-store, must-revalidate' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			$csvOutput = fopen( 'php://output', 'w' );
			$headers   = apply_filters( 'tajer_admin_reports_downloads_csv_file_headers', array(
				'id',
				'user_product',
				'product_id',
				'product_sub_id',
				'user_id',
				'file_id',
				'date',
				'ip'
			), $items );
			fputcsv( $csvOutput, $headers );
			foreach ( $items as $item ) {
				fputcsv( $csvOutput, $item );
			}
			fclose( $csvOutput );
			exit;
		}
	}

	function get_graph_data() {
		$nonce = $_REQUEST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'tajer_reports_nonce' ) ) {
			wp_die( 'Security check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_get_graph_data_capability', 'manage_options' ) ) ) {
			wp_die( 'Security Check' );
		}

		$Range = $_REQUEST['tajerRange'];
		if ( $Range == 'custom' ) {
			$From = $_REQUEST['tajerFrom'];
			$To   = $_REQUEST['tajerTo'];
			$this->getCustomGraphData( $From, $To );
		} else {
			$this->getDataFor( $Range );
		}

	}

	function response( $data ) {
		tajer_response( $data );
	}

	function getDataFor( $Range ) {
		switch ( $Range ) {
			case "today":
				$from = date( 'Y-m-d' ) . ' 00:00:00';
				$to   = date( 'Y-m-d H:i:s' );
				$this->plotGraph( $from, $to );
				break;
			case "yesterday":
				$from = date( "Y-m-d H:i:s", strtotime( "yesterday" ) );
				$to   = date( "Y-m-d", strtotime( "yesterday" ) ) . ' ' . '23:59:59';
				$this->plotGraph( $from, $to );
				break;
			case "current_month":
				$from = date( 'Y-m' ) . '-01 00:00:00';
				$to   = date( 'Y-m-d H:i:s' );
				$this->plotGraph( $from, $to );
				break;
			case "last_month":
				$from = date( 'Y-m-d', strtotime( "first day of previous month" ) ) . ' 00:00:00';
				$to   = date( 'Y-m-d', strtotime( "last day of previous month" ) ) . ' 23:59:59';
				$this->plotGraph( $from, $to );
				break;
			case "last_year":
				$from = date( 'Y', strtotime( "-1 year" ) ) . '-01-01 00:00:00';
				$to   = date( 'Y', strtotime( "-1 year" ) ) . '-12-31 23:59:59';
				$this->plotGraph( $from, $to );
				break;
			case "current_year":
				$from = date( 'Y' ) . '-01-01 00:00:00';
				$to   = date( 'Y-m-d H:i:s' );
				$this->plotGraph( $from, $to );
				break;
			case "last_week":
				$from = date( 'Y-m-d H:i:s', strtotime( "last week" ) );
				$to   = date( 'Y-m-d H:i:s', strtotime( "last week +7 day" ) );
				$this->plotGraph( $from, $to );
				break;
			case "current_week":
				$from = date( 'Y-m-d H:i:s', strtotime( "this week" ) );
				$to   = date( 'Y-m-d H:i:s' );
				$this->plotGraph( $from, $to );
				break;
			default:
				$from = add_filter( 'tajer_admin_reports_get_data_for_' . $Range . '_from' );
				$to   = add_filter( 'tajer_admin_reports_get_data_for_' . $Range . '_to' );
				$this->plotGraph( $from, $to );
				break;
		}
	}

	function getCustomGraphData( $from, $to ) {
		$this->plotGraph( $from, $to );
	}

	function admin_menu() {
		$tajer_reports_page_hook_suffix = add_submenu_page( 'edit.php?post_type=tajer_products', 'Reports', 'Reports', apply_filters( 'tajer_reports_admin_menu_capability', 'manage_options' ), 'tajer_reports', array(
			$this,
			'page'
		) );

		add_action( 'admin_print_scripts-' . $tajer_reports_page_hook_suffix, array(
			$this,
			'scripts'
		) );
	}

	function scripts() {
//		wp_enqueue_style( 'tajer-bootstrap', Tajer_URL . 'lib/bootstrap/css/bootstrap.min.css' );
//		wp_enqueue_style( 'tajer-bootstrap-theme', Tajer_URL . 'lib/bootstrap/css/bootstrap-theme.min.css', array(
//			'tajer-bootstrap'
//		) );
		wp_enqueue_style( 'tajer-semantic-ui', Tajer_URL . 'lib/semantic-ui/tajer-semantic-ui.css' );
		wp_enqueue_style( 'tajer-datetimepicker-css', Tajer_URL . 'lib/datetimepicker-master/jquery.datetimepicker.css' );
		wp_enqueue_style( 'chosen-jquery-css', Tajer_URL . 'lib/chosen_v1.2.0/chosen.min.css' );
		wp_enqueue_style( 'tajer-jquery-ui-css', Tajer_URL . 'lib/jquery-ui/jquery-ui.min.css' );
		wp_enqueue_style( 'tajer-admin-css', Tajer_URL . 'css/admin/reports.css', array(
			'chosen-jquery-css',
			'tajer-jquery-ui-css',
			'tajer-datetimepicker-css',
			'tajer-semantic-ui'
		) );


		wp_enqueue_script( 'tajer-datetimepicker-js', Tajer_URL . 'lib/datetimepicker-master/jquery.datetimepicker.js', array( 'jquery' ) );
//		wp_enqueue_script( 'tajer-bootstrap-js', Tajer_URL . 'lib/bootstrap/js/bootstrap.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'tajer-semantic-ui-js', Tajer_URL . 'lib/semantic-ui/semantic.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'chosen-jquery-js', Tajer_URL . 'lib/chosen_v1.2.0/chosen.jquery.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'flot-jquery-js', Tajer_URL . 'lib/flot/jquery.flot.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'flot-time-jquery-js', Tajer_URL . 'lib/flot/jquery.flot.time.min.js', array( 'flot-jquery-js' ) );
		wp_enqueue_script( 'tajer-moment-js', Tajer_URL . 'lib/js/moment.js' );
		wp_enqueue_script( 'tajer-report-js', Tajer_URL . 'js/admin/reports.js', array(
			'tajer-semantic-ui-js',
			'chosen-jquery-js',
			'tajer-datetimepicker-js',
			'tajer-moment-js',
			'flot-time-jquery-js'
		) );
	}

	function page() {
		tajer_get_template_part( 'admin-reports-page' );
	}

	public function plotGraph( $from, $to ) {

		$items = Tajer_DB::get_items_statistics_by_buying_date( $from, $to );

		if ( ! empty( $items ) || ! is_null( $items ) ) {
			$sales    = 0;
			$earnings = 0;
			$dataset1 = array( 'label' => 'Sales', 'data' => array() );
			$dataset2 = array( 'label' => 'Earnings', 'data' => array() );
			$d        = &$dataset1['data'];
			$d2       = &$dataset2['data'];
			foreach ( $items as $item ) {
				$d[]  = array( 1000 * ( (int) ( strtotime( $item->buying_date ) ) ), (int) $item->quantity );
				$d2[] = array( 1000 * ( (int) ( strtotime( $item->buying_date ) ) ), (float) $item->earnings );
				$sales += (int) $item->quantity;
				$earnings += (float) $item->earnings;
			}
			$this->response( apply_filters( 'tajer_admin_reports_plot_graph_response', array(
				'message'  => '',
				'sales'    => $sales,
				'earnings' => tajer_number_to_currency( $earnings ),
				'flotData' => array( $dataset1, $dataset2 )
			), $from, $to, $items ) );
		} else {
			$this->response( array(
				'message' => 'error'
			) );
		}
	}
}
