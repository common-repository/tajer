<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<div class="Tajer">
	<div class="tajer-reports tajer-container">
		<div class="ui padded grid">
			<div class="row">
				<h3 class="ui header"><?php esc_html_e( 'Reports', 'tajer' ); ?></h3>
			</div>
			<div class="row">
				<div class="eleven wide column">
					<div id="tajer-user-reports">
						<form class="ui fluid form tajer-reports-form">
							<div class="ui pointing secondary menu">
								<a class="item active tajer-tab-nav" data-tajer-type="analytics"
								   data-tab="analytics"><?php esc_html_e( 'Analytics', 'tajer' ); ?></a>
								<a class="item tajer-tab-nav" data-tab="report"
								   data-tajer-type="reports"><?php esc_html_e( 'Reports', 'tajer' ); ?></a>
							</div>
							<div class="ui tab segment active" data-tab="analytics">


								<div class="field tajer-field">
									<label for="range"></label>
									<select name="tajer-range" id="range" class="ui dropdown">
										<option
											value="current_month"><?php esc_html_e( 'Current Month', 'tajer' ); ?></option>
										<option
											value="last_month"><?php esc_html_e( 'Last Month', 'tajer' ); ?></option>
										<option value="today"><?php esc_html_e( 'Today', 'tajer' ) ?></option>
										<option value="yesterday"><?php esc_html_e( 'Yesterday', 'tajer' ); ?></option>
										<option
											value="current_week"><?php esc_html_e( 'Current Week', 'tajer' ); ?></option>
										<option value="last_week"><?php esc_html_e( 'Last Week', 'tajer' ); ?></option>
										<option
											value="current_year"><?php esc_html_e( 'Current Year', 'tajer' ); ?></option>
										<option value="last_year"><?php esc_html_e( 'Last Year', 'tajer' ); ?></option>
										<option value="custom"><?php esc_html_e( 'Custom', 'tajer' ); ?></option>
									</select>
								</div>
								<div id="custom-filter">
									<div class="field tajer-field">
										<label for="tajer-fileType"></label>
										<input type="text" class="tajer-date" name="tajer-from"/>
									</div>
									<div class="field tajer-field">
										<label for="tajer-fileType"></label>
										<input type="text" class="tajer-date" name="tajer-to"/>
									</div>
									<button id="tajer-filter"
									        class="fluid ui blue button"><?php esc_html_e( 'Filter', 'tajer' ); ?></button>
								</div>
								<div id="flot-placeholder" style="width:780px;height:780px"></div>
							</div>
							<div class="ui tab segment" data-tab="report">
								<div class="field tajer-field">
									<label for="tajer-report-for"><?php _e( 'Report For', 'tajer' ); ?></label>
									<select class="ui dropdown" name="tajer-report-for" id="tajer-report-for">
										<option value="sales">Sales</option>
										<option value="downloads">Downloads</option>
									</select>
								</div>
								<div class="field tajer-field">
									<label for="tajer-fileType"><?php _e( 'File Type', 'tajer' ); ?></label>
									<select class="ui dropdown" name="tajer-fileType" id="tajer-fileType">
										<option value="csv">CSV</option>
										<option value="pdf">PDF</option>
									</select>
								</div>
								<div class="field tajer-field">
									<label for="tajer-export-from"><?php _e( 'From', 'tajer' ); ?></label>
									<input type="text" class="tajer-date" name="tajer-export-from"
									       id="tajer-export-from"
									       placeholder="<?php _e( 'From this date', 'tajer' ); ?>">
								</div>

								<div class="field tajer-field">
									<label for="tajer-export-to"><?php _e( 'To', 'tajer' ); ?></label>
									<input type="text" class="tajer-date" name="tajer-export-to" id="tajer-export-to"
									       placeholder="<?php _e( 'To this date', 'tajer' ); ?>">
								</div>

								<?php wp_nonce_field( 'tajer_reports_nonce', 'tajer_reports_nonce_field' ); ?>
								<input type="submit" name="tajer-generate-file"
								       value="<?php _e( 'Generate File', 'tajer' ); ?>"
								       class="ui button"/>
							</div>
						</form>
					</div>
				</div>
				<div class="five wide column">
					<div class="ui segment tajer-saving">
						<div id="tajer-graph-details">
							<p id="tajer-sales"><?php _e( 'Total sales for period shown: ', 'tajer' ); ?><span>0</span>
							</p>

							<p id="tajer-earnings"><?php _e( 'Total earnings for period shown: ', 'tajer' ); ?>
								<?php echo tajer_number_to_currency( 0, true, 'span' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
