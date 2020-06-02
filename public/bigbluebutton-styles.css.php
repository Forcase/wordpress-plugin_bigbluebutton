:root {
<?php
$colors = [
'--color-white' => '#FFF !important',
'--color-off-white' => '#eaeef1 !important',
'--color-black' => '#000000 !important',
'--color-gray' => '#000100 !important',
'--color-gray-dark' => '#000100 !important',
'--color-gray-light' => '#7e8588 !important',
'--color-gray-darkest' => '#0e0f0a !important',
'--color-gray-lighter' => '#be1d28 !important',
'--color-gray-lightest' => '#d4d7da !important',
'--color-blue-light' => '#609ed5 !important',
'--color-blue-lighter' => '#a4c4e4 !important',
'--color-blue-lightest' => '#eaeff4 !important',
'--color-primary' => '#be1d28 !important',
'--color-success' => '#1e8252 !important',
'--color-danger' => '#d40f14 !important',
'--color-warning' => '#70247f !important',
'--color-link-hover' => '#457abd !important',
'--color-transparent' => 'transparent !important',
'--color-white-with-transparency' => '#ffffff40 !important',
'--toolbar-list-bg' => '#d8d8da !important',
'--toolbar-list-bg-focus' => '#c8ccd0 !important',
'--poll-annotation-gray' => '#2d2e2b !important',
'--list-item-bg-hover' => '#dce4ed !important',
'--poll-blue' => '#306db3 !important',
'--poll-stats-border-color' => '#d4d7da !important',
'--systemMessage-background-color' => '#f9f9fa !important',
'--systemMessage-border-color' => '#c5cace !important',
'--background-active' => '#eaeaeb !important',
];

foreach ($colors as $var => $value) {
		$rid = $_GET['rid'] ?? false;
		echo $var . ': ' . (get_field('bbb_' . $var, $rid) ?: $value) . ";\n" ;
}

?>

	--loader-bg: var(--color-gray-dark) !important;
	--loader-bg: red !important;
	--loader-bullet: var(--color-white) !important;
	--loader-message-color: var(--color-white) !important;
	--color-background: var(--color-gray-dark) !important;
	--color-text: var(--color-gray) !important;
	--color-heading: var(--color-gray-dark) !important;
	--color-link: var(--color-primary) !important;
	--color-gray-label: var(--color-gray) !important;
	--toolbar-button-color: var(--btn-default-color) !important;
	--toolbar-button-border-color: var(--color-gray-lighter) !important;
	--toolbar-list-color: var(--color-gray) !important;
	--unread-messages-bg: var(--color-danger) !important;
	--user-list-text: var(--color-gray) !important;
	--user-list-bg: var(--color-off-white) !important;
	--user-thumbnail-border: var(--color-gray-light) !important;
	--voice-user-bg: var(--color-success) !important;
	--voice-user-text: var(--color-white) !important;
	--moderator-text: var(--color-white) !important;
	--moderator-bg: var(--color-primary) !important;
	--sub-name-color: var(--color-gray-light) !important;
	--user-icons-color: var(--color-gray-light) !important;
	--user-icons-color-hover: var(--color-gray) !important;
	--item-focus-border: var(--color-blue-lighter) !important;
	--nb-default-color: var(--color-gray) !important;
	--nb-default-bg: var(--color-white) !important;
	--nb-default-border: var(--color-white) !important;
	--nb-primary-color: var(--color-white) !important;
	--nb-primary-bg: var(--color-primary) !important;
	--nb-primary-border: var(--color-primary) !important;
	--nb-success-color: var(--color-white) !important;
	--nb-success-bg: var(--color-success) !important;
	--nb-success-border: var(--color-success) !important;
	--nb-danger-color: var(--color-white) !important;
	--nb-danger-bg: var(--color-danger) !important;
	--nb-danger-border: var(--color-danger) !important;
	--dropdown-bg: var(--color-white) !important;
	--dropdown-color: var(--color-text) !important;
	--caret-shadow-color: var(--color-gray) !important;
	--user-avatar-border: var(--color-gray-light) !important;
	--user-avatar-text: var(--color-white) !important;
	--user-indicator-voice-bg: var(--color-success) !important;
	--user-indicator-muted-bg: var(--color-danger) !important;
	--btn-default-color: var(--color-gray) !important;
	--btn-default-bg: var(--color-white) !important;
	--btn-default-border: var(--color-white) !important;
	--btn-primary-color: var(--color-white) !important;
	--btn-primary-bg: var(--color-primary) !important;
	--btn-primary-border: var(--color-primary) !important;
	--btn-success-color: var(--color-white) !important;
	--btn-success-bg: var(--color-success) !important;
	--btn-success-border: var(--color-success) !important;
	--btn-danger-color: var(--color-white) !important;
	--btn-danger-bg: var(--color-danger) !important;
	--btn-danger-border: var(--color-danger) !important;
	--btn-dark-color: var(--color-white) !important;
	--btn-dark-bg: var(--color-gray-dark) !important;
	--btn-dark-border: var(--color-danger) !important;
	--toast-default-color: var(--color-white) !important;
	--toast-default-bg: var(--color-gray) !important;
	--toast-info-color: var(--color-white) !important;
	--toast-info-bg: var(--color-primary) !important;
	--toast-success-color: var(--color-white) !important;
	--toast-success-bg: var(--color-success) !important;
	--toast-error-color: var(--color-white) !important;
	--toast-error-bg: var(--color-danger) !important;
	--toast-warning-color: var(--color-white) !important;
	--toast-warning-bg: var(--color-warning) !important;
	--background: var(--color-white) !important;
}

body {
	background-color: #000100 !important;
}

.ReactModal__Overlay--after-open {
	background-color: var(--color-gray) !important;
}
