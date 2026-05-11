<?php

class WooCommerceExport {
	public function handle() {
//		error_log( 'WooCommerceExportStarted' );
//		sleep(10);
//		if (is_plugin_active('woocommerce/woocommerce.php')) {

		$paged = 1;
		$header = [];
		$rows = [];
		while(true) {
			$args = array(
				'type'     => 'shop_order',
				'status'   => array_keys( wc_get_order_statuses() ),
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'         => 'utm',
						'compare'     => 'LIKE',
						'compare_key' => 'LIKE',
					),
					array(
						'key'         => 'handl',
						'compare'     => 'LIKE',
						'compare_key' => 'LIKE',
					),
				),
				'orderby'  => 'date',
				'order'    => 'DESC',
				'page'     => $paged,
				'limit'    => 100,
				'paginate' => true,
			);
			$result = wc_get_orders( $args );
			/** @var WC_Order[] $orders */
			$orders        = $result->orders;
			$max_num_pages = $result->max_num_pages;

			foreach ( $orders as $order ) {
				$cur_data = [];
				/** @var WC_Order $order */
				$order_data     = $order->get_data();
				$total          = $order->get_total();
				$user_id        = $order->get_user_id();
				$order_status   = $order->get_status();
				$currency       = $order->get_currency();
				$payment_method = $order->get_payment_method();
				/** @var WC_DateTime $date_created */
				$date_created = $order->get_date_created();

				$utms = extractUTMsFromWooOrder( $order_data['meta_data'] );

				array_push( $cur_data, $order->get_id() );
				array_push( $cur_data, date( "Y-m-d H:i:s", $date_created->getTimestamp() ) );
				array_push( $cur_data, $order_status );
				array_push( $cur_data, $currency );
				array_push( $cur_data, $total );
				array_push( $cur_data, $payment_method );
				array_push( $cur_data, $user_id );
				$cur_data = array_merge( $cur_data, array_values( $order_data['billing'] ) );
				$cur_data = array_merge( $cur_data, array_values( $utms ) );

				if ( empty( $rows ) ) {
					array_push( $header, "order_id" );
					array_push( $header, "date_created" );
					array_push( $header, "order_status" );
					array_push( $header, "currency" );
					array_push( $header, "total" );
					array_push( $header, "payment_method" );
					array_push( $header, "user_id" );
					$header = array_merge( $header, array_keys( $order_data['billing'] ) );
					$header = array_merge( $header, array_keys( $utms ) );
					array_push( $rows, $header );
				}
				array_push( $rows, $cur_data );
			}

			if ( $paged >= min( $max_num_pages, 6 ) ) {
				break;
			}
			$paged++;
		}

//			dd($rows);

			header('Content-Type: text/csv; charset=UTF-8');
			header('Content-Disposition: attachment; filename=HandL_WooCommerceOrders.csv');
			header('Pragma: no-cache');
			header('Expires: 0');
			$fp = fopen('php://output', 'w');
			foreach ($rows as $row)
			{
				fputcsv($fp,$row);
			}
			fclose($fp);
			exit();
//		}

//		error_log( 'WooCommerceExportEnded' );
	}
}

function extractUTMsFromWooOrder($metas){
	$fields = generateUTMFields();
	$utms = [];
	foreach ( $fields as $field ) {
		/** @var WC_Meta_Data $meta */
		$value = '';
		foreach ($metas as $meta) {
			$meta_data = $meta->get_data();
			if ( $meta_data['key'] == $field ){
				$value = $meta_data['value'];
			}
		}
		$utms[$field] = $value;
	}
	return $utms;
}
