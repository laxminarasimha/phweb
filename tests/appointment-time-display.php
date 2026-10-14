<?php
/** Patient-free clock regression checks. Run: php tests/appointment-time-display.php */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

// Loading the plugin functions must not activate modules, run hooks or access a DB.
function register_activation_hook( $file, $callback ) {}
function add_action( $hook, $callback, ...$options ) {}
function add_shortcode( $tag, $callback ) {}

$clock_site_timezone = 'Asia/Kolkata';

function wp_timezone() {
    global $clock_site_timezone;
    return new DateTimeZone( $clock_site_timezone );
}

// Model wp_date's documented Unix-timestamp and site-timezone behaviour.
function wp_date( $format, $timestamp = null, $timezone = null ) {
    $timestamp = $timestamp === null ? time() : $timestamp;
    $timezone  = $timezone ?: wp_timezone();
    return ( new DateTimeImmutable( '@' . $timestamp ) )
        ->setTimezone( $timezone )
        ->format( $format );
}

$modules = array(
    'ph_appointments_format_local_time' =>
        ABSPATH . 'wp-content/plugins/prasanthi-hospitals-appointments/prasanthi-hospitals-appointments.php',
    'ph_patients_format_local_time' =>
        ABSPATH . 'wp-content/plugins/prasanthi-hospitals-patients/prasanthi-hospitals-patients.php',
);

foreach ( $modules as $helper => $path ) {
    $source = file_get_contents( $path );
    token_get_all( $source, TOKEN_PARSE );
    require $path;

    if ( substr_count( $source, $helper . '(' ) !== 2 ) {
        throw new RuntimeException( 'Expected one helper declaration and one display call: ' . $helper );
    }

    if ( preg_match( '/strtotime\s*\(\s*\$appointment->confirmed_time/', $source ) ) {
        throw new RuntimeException( 'A UTC-assuming confirmed-time display remains: ' . $path );
    }
}

echo 'PHP ' . PHP_VERSION . "\n";
echo "PASS: both full plugin modules parse; both displays use their clock helpers.\n";

// This is the original defect: a synthetic local time is shown 330 minutes late.
date_default_timezone_set( 'UTC' );
if ( wp_date( 'g:i A', strtotime( '09:15:00' ) ) !== '2:45 PM' ) {
    throw new RuntimeException( 'The regression fixture did not reproduce the original UTC-to-IST error.' );
}
echo "PASS: the old formatter reproduces the +330-minute regression.\n";

$cases = array(
    array( '00:00', '12:00 AM' ),
    array( '00:00:00', '12:00 AM' ),
    array( '09:05', '9:05 AM' ),
    array( '09:05:07', '9:05 AM' ),
    array( '12:00', '12:00 PM' ),
    array( '12:00:00', '12:00 PM' ),
    array( '18:45', '6:45 PM' ),
    array( '18:45:00', '6:45 PM' ),
    array( '23:59', '11:59 PM' ),
    array( '23:59:59', '11:59 PM' ),
    array( ' 09:05 ', '9:05 AM' ),
    array( '', '—' ),
    array( null, '—' ),
    array( '24:00', '—' ),
    array( '12:60', '—' ),
    array( '12:00:60', '—' ),
    array( '9:05', '—' ),
    array( '10:30:0', '—' ),
    array( '01:02:03:04', '—' ),
    array( '-01:00', '—' ),
    array( 'not-a-time', '—' ),
    array( '2026-01-02 10:30:00', '—' ),
);

$checks = 0;
foreach ( array( 'UTC', 'Europe/London' ) as $php_timezone ) {
    date_default_timezone_set( $php_timezone );

    foreach ( array( 'Asia/Kolkata', '+05:30', 'America/New_York' ) as $site_timezone ) {
        $clock_site_timezone = $site_timezone;

        foreach ( array_keys( $modules ) as $helper ) {
            foreach ( $cases as $case ) {
                $actual = $helper( $case[0] );
                if ( $actual !== $case[1] ) {
                    throw new RuntimeException(
                        $helper . ' failed for ' . var_export( $case[0], true ) .
                        ' with PHP timezone ' . $php_timezone . ' and site timezone ' . $site_timezone .
                        ': expected ' . $case[1] . ', got ' . $actual
                    );
                }
                ++$checks;
            }
        }
    }
}

echo 'PASS: ' . $checks . " clock checks across two helpers, three site timezones and two PHP timezones.\n";
echo "No WordPress bootstrap, patient data, database, or provider calls were used.\n";
