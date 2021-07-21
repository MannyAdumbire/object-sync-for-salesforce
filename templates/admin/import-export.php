<?php
/**
 * The form to import and export data for this plugin.
 *
 * @package Object_Sync_Salesforce
 */

?>

<h3><?php echo esc_html__( 'Import', 'object-sync-for-salesforce' ); ?></h3>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="import">
	<input type="hidden" name="action" value="object_sync_for_salesforce_import">
	<?php wp_nonce_field( 'object_sync_for_salesforce_nonce_import', 'object_sync_for_salesforce_nonce_import' ); ?>
	<p><?php esc_html__( 'Import the plugin data from a .json file. You can use the Export options below to get this file.', 'object-sync-for-salesforce' ); ?></p>
	<p>
		<input type="file" name="import_file" id="object-sync-for-salesforce-import" required>
	</p>
	<p>
		<input type="checkbox" value="1" name="overwrite" id="object-sync-for-salesforce-import-overwrite">
		<label for="object-sync-for-salesforce-import-overwrite"><?php echo esc_html__( 'Overwrite Existing Data', 'object-sync-for-salesforce' ); ?></label>
	</p>
	<input type="submit" class="button button-primary" value="<?php echo esc_html__( 'Import', 'object-sync-for-salesforce' ); ?>" />
</form>

<h3><?php echo esc_html__( 'Export', 'object-sync-for-salesforce' ); ?></h3>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="export">
	<input type="hidden" name="action" value="object_sync_for_salesforce_export">
	<?php wp_nonce_field( 'object_sync_for_salesforce_nonce_export', 'object_sync_for_salesforce_nonce_export' ); ?>
	<p>
		<input type="checkbox" value="fieldmaps" name="export[]" id="object-sync-for-salesforce-export-fieldmaps">
		<label for="object-sync-for-salesforce-export-fieldmaps"><?php echo esc_html__( 'Include Fieldmaps', 'object-sync-for-salesforce' ); ?></label>
	</p>
	<p>
		<input type="checkbox" value="object_maps" name="export[]" id="object-sync-for-salesforce-export-object-maps" />
		<label for="object-sync-for-salesforce-export-object-maps"><?php echo esc_html__( 'Include Object Maps', 'object-sync-for-salesforce' ); ?></label>
	</p>
	<p>
		<input type="checkbox" value="plugin_settings" name="export[]" id="object-sync-for-salesforce-export-plugin-settings" />
		<label for="object-sync-for-salesforce-export-plugin-settings"><?php echo esc_html__( 'Include Plugin Settings', 'object-sync-for-salesforce' ); ?></label>
	</p>
	<input type="submit" class="button button-primary" value="<?php echo esc_html__( 'Export', 'object-sync-for-salesforce' ); ?>" />
</form>
