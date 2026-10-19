<?php
/**
 * Recurrence RRULE utility.
 *
 * @package WP_Event_Calendar/iCalendar/RRULE
 */
namespace WP_Event_Calendar\Utilities\iCalendar\Recur;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class for generating recurring Event sequences from a set of VEVENT and RRULE
 * parameters from the iCalendar RFC 5545 specification.
 *
 * See: https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
 */
class Sequence {

	/** Arguments *************************************************************/

	/**
	 * The original array of arguments.
	 *
	 * @var array
	 */
	public $args = array();

	/** Settings **************************************************************/

	/**
	 * Return format.
	 *
	 * @var mixed
	 */
	public $format = 'Y-m-d H:i:s';

	/**
	 * Sequence occurrences even when not in range.
	 *
	 * @var bool
	 */
	public $sequence = true;

	/**
	 * Avoid infinite loops by breaking at this number.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $max = 1000;

	/** Boundaries ************************************************************/

	/**
	 * Start of range to compute dates in.
	 *
	 * @var string
	 */
	protected $after;

	/**
	 * End of range to compute dates in.
	 *
	 * @var string
	 */
	protected $before;

	/** VEVENT parameters *****************************************************/

	/**
	 * The unique ID of this Event.
	 *
	 * @var mixed
	 */
	protected $id;

	/**
	 * Time Zone Identifier.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.2.19
	 * @var string
	 */
	protected $tzid;

	/**
	 * Date Time Start.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.6.1
	 * @var string
	 */
	protected $dtstart;

	/**
	 * Date Time End.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.6.1
	 * @var string
	 */
	protected $dtend;

	/**
	 * Exception Date Times.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.8.5.1
	 * @var array
	 */
	protected $exdate = array();

	/**
	 * Recurrence Date Times.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.8.5.2
	 * @var array
	 */
	protected $rdate = array();

	/** RRULE parameters ******************************************************/

	/**
	 * Recurrence Frequency.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var string
	 */
	protected $freq;

	/**
	 * Recurrence Interval.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var int
	 */
	protected $interval = 1;

	/**
	 * Recurrence Count.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var int
	 */
	protected $count;

	/**
	 * Recurrence Until.
	 *
	 * End Date.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var string
	 */
	protected $until;

	/**
	 * Recurrence by Month.
	 *
	 * +/- 1 to 12
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $bymonth = array();

	/**
	 * Recurrence by Week Number.
	 *
	 * +/- 1 to 53
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $byweekno = array();

	/**
	 * Recurrence by Year Day Number.
	 *
	 * +/- 1 to 366
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $byyearday = array();

	/**
	 * Recurrence by Month Day Number.
	 *
	 * +/- 1 to 31
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $bymonthday = array();

	/**
	 * Recurrence by Day Abbreviation.
	 *
	 * SU, MU, TU, WE, TH, FR, SA
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $byday = array();

	/**
	 * Recurrence by Hour.
	 *
	 * 0 to 23
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $byhour = array();

	/**
	 * Recurrence by Minute.
	 *
	 * 0 to 59
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $byminute = array();

	/**
	 * Recurrence by Second.
	 *
	 * 0 to 59
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $bysecond = array();

	/**
	 * Recurrence by Position.
	 *
	 * +/- 1 to 366
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $bysetpos = array();

	/**
	 * Recurrence Week Start.
	 *
	 * SU, MU, TU, WE, TH, FR, SA
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var string
	 */
	protected $wkst = 'MO';

	/** Stashes ***************************************************************/

	/**
	 * DateTimeZone object.
	 *
	 * @var DateTimeZone
	 */
	protected $timezone;

	/**
	 * DateTime object.
	 *
	 * @var DateTime
	 */
	protected $datetime;

	/**
	 * DateInterval object.
	 *
	 * @var DateInterval
	 */
	protected $duration;

	/**
	 * Duration in seconds.
	 *
	 * @var int
	 */
	protected $duration_time;

	/**
	 * All expansions.
	 *
	 * @var array
	 */
	protected $expansions = array();

	/**
	 * All limitations.
	 *
	 * @var array
	 */
	protected $limitations = array();

	/**
	 * Number of expansions set by rule.
	 *
	 * @var int
	 */
	protected $expansion_count = 0;

	/**
	 * Number of iterations.
	 *
	 * @var int
	 */
	protected $iteration = 0;

	/**
	 * Number of occurrences.
	 *
	 * @var int
	 */
	protected $current_count = 0;

	/**
	 * Last computed date.
	 *
	 * @var string
	 */
	protected $current_date;

	/**
	 * Week sequence.
	 *
	 * @var array
	 */
	protected $wkst_seq = array( 'MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU' );

	/** Caches ****************************************************************/

	/**
	 * Previously computed dates.
	 *
	 * @var array
	 */
	protected $cached_dates = array();

	/**
	 * Dates from rdate rule.
	 *
	 * @var array
	 */
	protected $cached_rdates = array();

	/**
	 * Cached year, month and week details.
	 *
	 * @var array
	 */
	protected $cached_details = array();

	/** Errors ****************************************************************/

	/**
	 * True if error found in arguments.
	 *
	 * @var bool
	 */
	public $error = false;

	/** Constants *************************************************************/

	/**
	 * Constant for days of week.
	 *
	 * @var array
	 */
	const DAYS = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );

	/** Methods ***************************************************************/

	/**
	 * Constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {

		// Bail if no arguments to parse
		if ( empty( $args ) ) {
			return;
		}

		// Parse arguments
		$this->parse_args( $args );
	}

	/**
	 * Parse the arguments.
	 *
	 * @param array $args
	 * @return bool
	 */
	protected function parse_args( $args = array() ) {

		// Bail if no arguments
		if ( empty( $args ) ) {
			$this->error = true;
			return false;
		}

		// Set the original arguments
		$this->args = $args;

		// Reset the error flag
		$this->error = false;

		// Lowercase argument keys
		$args = $this->lc_keys( $args );

		// ID is required early
		$this->id = ! empty( $args[ 'id' ] )
			? $args[ 'id' ]
			: null;

		// Time zone is required early
		$this->tzid = ! empty( $args[ 'tzid' ] ) && is_string( $args[ 'tzid' ] )
			? trim( $args[ 'tzid' ] )
			: date_default_timezone_get();

		// Get time zone object
		try {
			$this->timezone = new \DateTimeZone( $this->tzid );

		// Bail on error
		} catch ( \Exception $e ) {
			$this->error = true;

			return false;
		}

		// Parse RRULE if present
		if ( ! empty( $args[ 'rrule' ] ) && strstr( $args[ 'rrule' ], '=' ) ) {

			// Explode by semicolon
			$rules = $this->safe_explode( ';', $args[ 'rrule' ] );

			// Successfully exploded
			if ( ! empty( $rules ) ) {

				// Loop through parts
				foreach ( $rules as $part ) {

					// Skip if invalid
					if ( ! strstr( $part, '=' ) ) {
						continue;
					}

					// Explode to get rule and value
					list( $rule, $value ) = explode( '=', $part );

					// Lowercase the key
					$rule = strtolower( $rule );

					// Avoid unknown properties and duplicates
					if ( property_exists( $this, $rule ) && ! array_key_exists( $rule, $args ) ) {
						$args[ $rule ] = $value;
					}
				}
			}
		}

		// Loop through arguments
		foreach ( $args as $key => $value ) {

			// Skip empty string values
			if ( empty( $value ) && is_string( $value ) ) {
				continue;
			}

			// Validate specific args
			switch ( $key ) {

				// DateTime::format() compatible string to return
				case 'format' :

					// Format to return
					if ( ! empty( $args[ 'format' ] ) && is_string( $args[ 'format' ] ) ) {
						$this->format = trim( $args[ 'format' ] );
					}

					break;

				// Prevent infinite loops
				case 'max' :

					// Maximum number of iterations
					if ( ! empty( $args[ 'max' ] ) && is_numeric( $args[ 'max' ] ) ) {
						$this->max = abs( $args[ 'max' ] );
					}

					break;

				// Whether or not to include sequences
				case 'sequence' :

					// Return sequence numbers?
					if ( isset( $args[ 'sequence' ] ) ) {
						$this->sequence = (bool) $args[ 'sequence' ];
					}

					break;

				// DATETIME || DATE values
				case 'dtstart' :
				case 'dtend' :
				case 'until' :
				case 'after' :
				case 'before' :

					// Create timestamps
					$this->{$key} = $this->strtotime( $value );

					// Invalid
					if ( false === $this->{$key} ) {
						$this->error = true;
					}

					break;

				// RDATE
				case 'rdate' :

					// Make sure value is an array
					if ( ! is_array( $value ) ) {
						$value = array( $value );
					}

					// Loop through values
					foreach ( $value as $entry ) {

						// Explode by comma
						$rdate = $this->safe_explode( ',', $entry );

						// Loop through dates
						foreach ( $rdate as $property ) {
							$end      = null;
							$duration = null;

							// Explode to get date & period
							$parts  = array_pad( explode( '/', $property, 2 ), 2, '' );
							$date   = $parts[ 0 ];
							$period = $parts[ 1 ];

							// Create timestamp
							$start = $this->strtotime( $date );

							// Invalid
							if ( false === $start ) {
								$this->error = true;
							}

							// Try to get duration
							if ( ! empty( $period ) ) {
								try {
									$duration = new \DateInterval( $period );

								// Evaluate 2nd part as duration
								} catch ( \Exception $e ) {
									$duration = null;

									// Evaluate 2nd part as datestring
									$end = $this->strtotime( $period );

									// Invalid
									if ( false === $end ) {
										$this->error = true;
									}
								}
							}

							// Add recur date to array
							$this->rdate[] = array(
								'start'    => $start,
								'end'      => $end,
								'duration' => $duration
							);
						}
					}

					break;

				// EXDATE
				case 'exdate' :

					// Make sure value is an array
					if ( ! is_array( $value ) ) {
						$value = array( $value );
					}

					// Loop through values
					foreach ( $value as $entry ) {

						// Explode by comma
						$exdate = $this->safe_explode( ',', $entry );

						// Loop through dates
						foreach ( $exdate as $datestring ) {

							// Create timestamp
							$this->exdate[] = $exclude = $this->strtotime( $datestring );

							// Invalid
							if ( false === $exclude ) {
								$this->error = true;
							}
						}
					}

					break;

				// DURATION
				case 'duration' :

					// Try to get duration
					try {
						$this->duration = new \DateInterval( $value );
					} catch ( \Exception $e ) {
						$this->error = false;
					}

					break;

				// FREQUENCY
				case 'freq' :

					// Convert to uppercase
					$this->freq = strtoupper( $value );

					// Valid frequencies
					$valid = array( 'SECONDLY', 'MINUTELY', 'HOURLY', 'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY' );

					// Invalid
					if ( ! in_array( $this->freq, $valid, true ) ) {
						$this->error = true;
					}

					break;

				// NUMERIC VALUES (> 0)
				case 'count' :
				case 'interval' :

					// Cast to integer
					$this->{$key} = (int) $value;

					// Invalid
					if ( ! is_numeric( $this->{$key} ) || ( $this->{$key} < 1 ) ) {
						$this->error = true;
					}

					break;

				// WKST
				case 'wkst' :

					// Convert to uppercase
					$this->wkst = strtoupper( $value );

					// Invalid
					if ( ! in_array( $this->wkst, self::DAYS, true ) ) {
						$this->error = true;
					}

					break;

				// BYMONTH (+/- 1 to 12)
				case 'bymonth' :

					// Explode by comma
					$this->bymonth = $this->safe_explode( ',', $value );

					foreach ( $this->bymonth as $month ) {

						// Invalid
						if ( ! is_numeric( $month ) || ( abs( $month ) < 1 ) || ( abs( $month ) > 12 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// BYWEEKNO (+/- 1 to 53)
				case 'byweekno' :

					// Explode by comma
					$this->byweekno = $this->safe_explode( ',', $value );

					foreach ( $this->byweekno as $week ) {

						// Invalid
						if ( ! is_numeric( $week ) || ( abs( $week ) < 1 ) || ( abs( $week ) > 53 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// BYYEARDAY (+/- 1 to 366)
				case 'byyearday' :

				// BYSETPOS (+/- 1 to 366)
				case 'bysetpos' :

					// Explode by comma
					$this->{$key} = $this->safe_explode( ',', $value );

					foreach ( $this->{$key} as $value ) {

						// Invalid
						if ( ! is_numeric( $value ) || ( abs( $value ) < 1 ) || ( abs( $value ) > 366 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// BYMONTHDAY (+/- 1 to 31)
				case 'bymonthday' :

					// Explode by comma
					$this->bymonthday = $this->safe_explode( ',', $value );

					foreach ( $this->bymonthday as $day ) {

						// Invalid
						if ( ! is_numeric( $day ) || ( abs( $day ) < 1 ) || ( abs( $day ) > 31 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// BYDAY (SU, MO, TU, WE, TH, FR, SA)
				case 'byday' :

					// Explode by comma
					$byday = $this->safe_explode( ',', $value );

					foreach ( $byday as $key ) {
						$weekday = substr( $key, -2 );
						$pos     = substr( $key, 0, -2 );

						// Set byday
						$this->byday[] = array(
							'weekday' => $weekday,
							'pos'     => $pos
						);

						// Invalid
						if ( ! in_array( $weekday, self::DAYS, true ) || ( ( $pos !== '' ) && ( ! is_numeric( $pos ) || ( $pos === 0 ) ) ) ) {
							$this->error = true;
						}
					}

					break;

				// BYHOUR (0-23)
				case 'byhour' :

					// Explode by comma
					$this->byhour = $this->safe_explode( ',', $value );

					foreach ( $this->byhour as $hour ) {

						// Invalid
						if ( ! is_numeric( $hour ) || ( $hour < 0 ) || ( $hour > 23 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// BYMINUTE (0-59)
				case 'byminute' :

				// BYSECOND (0-59)
				case 'bysecond' :

					// Explode by comma
					$this->{$key} = $this->safe_explode( ',', $value );

					foreach ( $this->{$key} as $value ) {

						// Invalid
						if ( ! is_numeric( $value ) || ( $value < 0 ) || ( $value > 59 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;
			}
		}

		// Validate arguments
		$this->validate_args();

		// Validate class properties from arguments
		$this->validate_properties();

		// Return
		return empty( $this->error );
	}

	/**
	 * Validate all class arguments.
	 */
	protected function validate_args() {

		// Synchronize until / before
		if ( ! is_null( $this->until ) && ( is_null( $this->before ) || ( $this->before > $this->until ) ) ) {
			$this->before = $this->until;
		}

		// Enable sequencing if counting / after
		if ( ! is_null( $this->count ) || is_null( $this->after ) ) {
			$this->sequence = true;
		}

		// End exists
		if ( ! is_null( $this->dtend ) ) {

			/**
			 * Calculate a nominal duration for anniversary or all-day events
			 * instead of an exact duration.
			 *
			 * The value type of dtstart is not recognized by this script, so
			 * events starting and ending on midnight are handled as all-day.
			 */
			if ( ( array( '00', '00', '00' ) === $this->date_explode( 'H-i-s', '-', $this->dtstart ) ) && ( array( '00', '00', '00' ) === $this->date_explode( 'H-i-s', '-', $this->dtend ) ) ) {
				$number_of_days = round( ( $this->dtend - $this->dtstart ) / 86400 );
				$this->duration = new \DateInterval( 'P' . $number_of_days . 'D' );
				$this->dtend    = null;

			// Set duration_time through simple subtraction
			} else {
				$this->duration_time = ( $this->dtend - $this->dtstart );
			}

		// No end
		} else {
			$this->duration_time = 0;
		}

		// Recur date exists
		if ( ! empty( $this->rdate ) ) {

			// Create rdate cache
			foreach ( $this->rdate as $option ) {
				$this->cached_rdates[] = $option[ 'start' ];
			}

			// Sort rdates
			array_multisort( $this->cached_rdates, SORT_NUMERIC, $this->rdate );

			// Exclude exdates
			if ( ! empty( $this->exdate ) ) {
				$this->cached_rdates = array_diff( $this->cached_rdates, $this->exdate );
			}
		}

		// Set the current date from start
		$this->current_date = $this->dtstart;

		/**
		 * Define expansions to do, and limitations to apply.
		 *
		 * Conflicting rules are grouped, and results from grouped expansions
		 * will be intersected.
		 *
		 * I.E.: BYYEARDAY=1,5,10;BYMONTHDAY=1,10 will be expanded to January 1, 10
		 */
		switch ( $this->freq ) {
			case 'YEARLY' :
				$this->expansions  = array( array( 'bymonth' ), array( 'byweekno' ), array( 'byyearday', 'bymonthday', 'byday' ), array( 'byhour' ), array( 'byminute' ), array( 'bysecond' ) );
				$this->limitations = array();
				break;

			case 'MONTHLY' :
				$this->expansions  = array( array( 'bymonthday', 'byday' ), array( 'byhour' ), array( 'byminute' ), array( 'bysecond' ) );
				$this->limitations = array( 'bymonth' );
				break;

			case 'WEEKLY' :
				$this->expansions  = array( array( 'byday' ), array( 'byhour' ), array( 'byminute' ), array( 'bysecond' ) );
				$this->limitations = array( 'bymonth' );
				break;

			case 'DAILY' :
				$this->expansions  = array( array( 'byhour' ), array( 'byminute' ), array( 'bysecond' ) );
				$this->limitations = array( 'bymonth', 'bymonthday', 'byday' );
				break;

			case 'HOURLY' :
				$this->expansions  = array( array( 'byminute' ), array( 'bysecond' ) );
				$this->limitations = array( 'bymonth', 'byyearday', 'bymonthday', 'byday', 'byhour' );
				break;

			case 'MINUTELY' :
				$this->expansions  = array( array( 'bysecond' ) );
				$this->limitations = array( 'bymonth', 'byyearday', 'bymonthday', 'byday', 'byhour', 'byminute' );
				break;

			case 'SECONDLY' :
				$this->expansions  = array();
				$this->limitations = array( 'bymonth', 'byyearday', 'bymonthday', 'byday', 'byhour', 'byminute', 'bysecond' );
				break;
		}

		// Count expansion to do
		foreach ( $this->expansions as $expansion_set ) {
			foreach ( $expansion_set as $expansion ) {
				if ( ! empty( $this->{$expansion} ) ) {
					$this->expansion_count++;
				}
			}
		}

		// Set weekday order according to wkst
		while ( $this->wkst !== $this->wkst_seq[ 0 ] ) {
			$this->wkst_seq[] = array_shift( $this->wkst_seq );
		}
	}

	/**
	 * Used after validating arguments, this method looks at those results and
	 * performs additional validation and error checking.
	 *
	 * If an error is found, meaning that invalid properties have been set, this
	 * method sets the $error property to true.
	 */
	protected function validate_properties() {

		// I can't believe this works...
		switch ( true ) {

			// Frequency cannot be empty
			case ( empty( $this->freq ) ) :

			// Timezone cannot be empty
			case ( empty( $this->timezone ) ) :

			// Start cannot be empty
			case ( is_null( $this->dtstart ) ) :

			// Duration time cannot be negative (0 is "no end")
			case ( ! is_null( $this->duration_time ) && ( 0 > $this->duration_time ) ) :

			// Duration cannot compete with End
			case ( ! empty( $this->duration ) && ! is_null( $this->dtend ) ) :

			// Count cannot compete with End Date
			case ( ! empty( $this->count    ) && ! is_null( $this->until ) ) :

			// By week not for Yearly frequency
			case ( ! empty( $this->byweekno   ) && ( 'YEARLY' !== $this->freq ) ) :

			// By month day not for Weekly frequency
			case ( ! empty( $this->bymonthday ) && ( 'WEEKLY' === $this->freq ) ) :

			// By year day not for Daily, Weekly, or Monthly frequencies
			case ( ! empty( $this->byyearday  ) && in_array( $this->freq, array( 'DAILY', 'WEEKLY', 'MONTHLY' ), true ) ) :
				$this->error = true;
		}
	}

	/**
	 * Move on to the next iteration.
	 *
	 * @return mixed
	 */
	public function next() {

		// Bail if error
		if ( ! empty( $this->error ) ) {
			return false;
		}

		// Bail if max count reached
		if ( ! empty( $this->count ) && ( $this->current_count >= $this->count ) ) {
			return false;
		}

		// Return cached dates first
		if ( ! empty( $this->cached_dates ) ) {
			return $this->get_result();
		}

		// Interval out of range
		if ( ! empty( $this->before ) && ( $this->current_date >= $this->before ) ) {

			// Rdates exist so get that result
			if ( ! empty( $this->cached_rdates ) ) {
				return $this->get_result();
			}

			// Bail
			return false;
		}

		// Default break to 0
		$safety = 0;

		// Start the open loop
		do {
			$this->iteration++;

			// Break after X number of failed iterations
			if ( ++$safety > $this->max ) {
				break;
			}

			// Apply interval from 2nd iteration onward (dtstart is first date to be expanded)
			if ( 1 < $this->iteration ) {
				$this->next_interval();
			}

			// Bail if current date out of range
			if ( $this->out_of_range() ) {
				return false;
			}

			// Bail if current date not in range
			if ( $this->not_in_range() ) {
				continue;
			}

			// Start expanding with current date
			$dates = array( $this->current_date );

			// No EXPANSIONS, apply LIMITATIONS only
			if ( empty( $this->expansion_count ) ) {

				// Skip if restricted by exdate
				if ( ! empty( $this->exdate ) && in_array( $this->current_date, $this->exdate, true ) ) {
					continue;
				}

				// Loop through limitations
				foreach ( $this->limitations as $limitation ) {

					// Function
					$func = 'limit_' . $limitation;

					// Skip if restricted by rule
					if ( ! empty( $this->{$limitation} ) && ! call_user_func( array( $this, $func ), $this->current_date ) ) {
						continue 2;
					}
				}

			// Has EXPANSIONS, may have LIMITATIONS
			} else {

				// Loop through expansions
				foreach ( $this->expansions as $expansion_set ) {
					$expanded_dates = array();

					// Loop through dates
					foreach ( $dates as $date ) {
						$result_dates = array();

						// Compute dates for each expansion in set
						foreach ( $expansion_set as $expansion ) {

							// Skip if no expansion
							if ( empty( $this->{$expansion} ) ) {
								continue 1;
							}

							// Function
							$func = 'expand_' . $expansion;

							// Add to result dates
							$result_dates[] = call_user_func( array( $this, $func ), $date );
						}

						// No expansions done - continue to next set
						if ( empty( $result_dates ) ) {
							continue 2;
						}

						// Merge result
						$this->merge_set( $expanded_dates, $result_dates );
					}

					// Set dates
					$dates = $expanded_dates;

					// Skip to next interval if no dates
					if ( empty( $dates ) ) {
						continue 2;
					}
				}

				// Remove doubles
				$dates = array_unique( $dates );

				// Reset limited dates
				$limited_dates = array();

				// Loop through dates
				foreach ( $dates as $date ) {

					// LIMITATIONS
					foreach ( $this->limitations as $limitation ) {

						// Function
						$func = 'limit_' . $limitation;

						// Restricted by rule - continue to next date
						if ( ! empty( $this->{$limitation} ) && ! call_user_func( array( $this, $func ), $date ) ) {
							continue 2;
						}
					}

					// Add date to limited dates
					$limited_dates[] = $date;
				}

				// Apply bysetpos
				if ( ! empty( $this->bysetpos ) ) {
					$limited_dates = $this->limit_bysetpos( $limited_dates );
				}

				// Apply exdate
				if ( ! empty( $this->exdate ) ) {
					$limited_dates = array_diff( $limited_dates, $this->exdate );
				}

				// Set dates
				$dates = $limited_dates;

				// Skip to next interval if no dates
				if ( empty( $dates ) ) {
					continue;
				}

				// Reset limited dates
				$limited_dates = array();

				// Loop through dates
				foreach ( $dates as $date ) {

					// Out of range
					if ( ! is_null( $this->before ) && ( $date > $this->before ) ) {
						continue;
					}

					// Skip if less than
					if ( $date < $this->dtstart ) {
						continue;
					}

					// Add date to limited dates
					$limited_dates[] = $date;
				}

				// Set dates
				$dates = $limited_dates;

				// Skip to next interval if no dates
				if ( empty( $dates ) ) {
					continue;
				}
			}

			// Cache dates
			$this->cached_dates = $dates;

			// Get result
			return $this->get_result();

		// Keep the loop alive
		} while ( 1 );

		// Iteration stopped, but rdates left
		if ( ! empty( $this->cached_rdates ) ) {
			return $this->get_result();
		}

		// Return default
		return false;
	}

	/**
	 * Get the result of this current iteration.
	 *
	 * @return mixed
	 */
	protected function get_result() {

		// If dtstart is not synchronized with the rrule
		if (

			// Count is 0
			( 0 === $this->current_count )

			&&

			// dtstart may be dropped before, according to the RFC
			( $this->dtstart != $this->cached_dates[ 0 ] )

			&&

			// but it can be returned anyways
			( $this->dtstart != current( $this->cached_rdates ) )

			&&

			// dtstart is not excluded
			! in_array( $this->dtstart, $this->exdate, true )
		) {

			// Set vars
			$start         =& $this->dtstart;
			$end           =& $this->dtend;
			$duration      =& $this->duration;
			$duration_time =& $this->duration_time;

		// Get RDATE
		} elseif (

			// Has cached recur dates
			! empty( $this->cached_rdates )

			&&

			(

				// Does not have cached dates
				empty( $this->cached_dates )

				||

				// Has less-or-equal recur dates than cached dates
				( current( $this->cached_rdates ) <= $this->cached_dates[ 0 ] )
			)
		) {

			// Get values
			$key           =      key( $this->cached_rdates );
			$start         =  current( $this->cached_rdates );
			$end           =& $this->rdate[ $key ][ 'end' ];
			$duration      =& $this->rdate[ $key ][ 'duration' ];
			$duration_time =& $this->duration_time;

			// Remove duplicate entry
			if ( $start == $this->cached_dates[ 0 ] ) {
				array_shift( $this->cached_dates );
			}

		// Get cached date
		} elseif ( ! is_null( $start = array_shift( $this->cached_dates ) ) ) {
			$duration      =& $this->duration;
			$duration_time =& $this->duration_time;

		// Proceed to next interval when no values left
		} else {
			return $this->next();
		}

		// End date time
		$end_date_time = $start + $duration_time;

		// Not in range
		if ( ! is_null( $this->after ) && ( $end_date_time < $this->after ) ) {
			$this->current_count++;

			// Done, done, onto the next one...
			return $this->get_result();
		}

		// Create result using start
		$retval = array(
			'dtstart' => $this->date( $this->format, $start )
		);

		// Calculate dtend from DURATION
		if ( ! empty( $duration ) ) {
			$retval[ 'dtend' ] = $this->date( $this->format, $start, $duration );

		// Calculate dtend from rdate end
		} elseif ( ! empty( $end ) ) {
			$retval[ 'dtend' ] = $this->date( $this->format, $end );

		// Calculate dtend from duration time
		} elseif ( ! empty( $duration_time ) ) {
			$retval[ 'dtend' ] = $this->date( $this->format, $end_date_time );

		// Default to "no end" and use dtstart
		} else {
			$retval[ 'dtend' ] = $retval[ 'dtstart' ];
		}

		// The original value of the "DTSTART" property of the recurrence instance
		$retval[ 'recurrence-id' ] = gmdate( 'Ymd\THis\Z', $start );

		// The numeric sequence of this instance
		if ( ! empty( $this->sequence ) && ( $this->current_count > 0 ) ) {
			$retval[ 'sequence' ] = $this->current_count;
		}

		// Remove used rdate from cache
		if ( isset( $key ) ) {
			unset( $this->cached_rdates[ $key ] );
		}

		// Bump the count
		$this->current_count++;

		// Return
		return $retval;
	}

	/**
	 * Used when iterating, this method bumps the current_date property forward
	 * by the interval, relative to the recurring frequency.
	 */
	protected function next_interval() {

		// Get date values
		list( $Y, $m, $d, $H, $i, $s ) = $this->date_explode( 'Y-m-d-H-i-s', '-', $this->current_date );

		switch ( $this->freq ) {
			case 'YEARLY' :
				$n = 1;

				// RFC 5545 ignores invalid dates instead of normalizing them.
				do {
					$year = $Y + ( $this->interval * $n );

					if ( $d <= $this->date( 't', $this->mktime( 0, 0, 0, $m, 1, $year ) ) ) {
						break;
					}

					$n++;
					$this->iteration++;
				} while ( $n <= $this->max );

				$this->current_date = $this->mktime( $H, $i, $s, $m, $d, $year );
				break;

			case 'MONTHLY' :

				// Default - take day part from dtstart
				if ( $d <= 28 ) {
					$this->current_date = $this->mktime( $H, $i, $s, $m + $this->interval, $d, $Y );

				// Day part is defined by rule:
				} elseif ( ! empty( $this->bymonthday ) || ! empty( $this->byday ) ) {
					$this->current_date = $this->mktime( $H, $i, $s, $m + $this->interval, 28, $Y );

				// Move day to a safe position
				} else {
					$n = 1;

					// Skip months with fewer days
					while ( $d > $this->date( 't', $this->mktime( 0, 0, 0, $m + $this->interval * $n, 1, $Y ) ) ) {
						$n++;
						$this->iteration++;
					}
					$this->current_date = $this->mktime( $H, $i, $s, $m + $this->interval * $n, $d, $Y );
				}
				break;

			case 'WEEKLY' :
				$this->current_date = $this->mktime( $H, $i, $s, $m, $d + $this->interval * 7, $Y );
				break;

			case 'DAILY' :
				$this->current_date = $this->mktime( $H, $i, $s, $m, $d + $this->interval, $Y );
				break;

			case 'HOURLY' :
				$this->current_date = $this->mktime( $H + $this->interval, $i, $s, $m, $d, $Y );
				break;

			case 'MINUTELY' :
				$this->current_date = $this->mktime( $H, $i + $this->interval, $s, $m, $d, $Y );
				break;

			case 'SECONDLY' :
				$this->current_date = $this->mktime( $H, $i, $s + $this->interval, $m, $d, $Y );
				break;
		}
	}

	/** Ranges ****************************************************************/

	/**
	 * Used when iterating, this method checks if an Event is out of range.
	 *
	 * @return bool
	 */
	protected function out_of_range() {

		// Bail if no before
		if ( is_null( $this->before ) ) {
			return false;
		}

		// Bail if no expansions - check current date only
		if ( empty( $this->expansion_count ) && ( $this->current_date > $this->before ) ) {
			return true;
		}

		// Check range of possible expansions
		switch ( $this->freq ) {
			case 'YEARLY' :
				$year_details = $this->get_year_details( $this->date( 'Y', $this->current_date ) );

				// Get start
				$start = ! empty( $this->byweekno ) && ( $year_details[ 'week_start' ] < $year_details[ 'start' ] )
					? $year_details[ 'week_start' ]
					: $year_details[ 'start' ];

				// Bail if out of range
				if ( $start > $this->before ) {
					return true;
				}

				break;

			case 'MONTHLY' :

				// Get date values
				list( $Y, $m ) = $this->date_explode( 'Y-m', '-', $this->current_date );

				// Get month details
				$month_details = $this->get_month_details( $m, $Y );

				// Bail if out of range
				if ( $month_details[ 'start' ] > $this->before ) {
					return true;
				}

				break;

			case 'WEEKLY' :

				// Get date values
				list( $Y, $z ) = $this->date_explode( 'Y-z', '-', $this->current_date );

				// Get week details
				$week_details = $this->get_week_details( $z, $Y );

				// Bail if out of range
				if ( $week_details[ 'start' ] > $this->before ) {
					return true;
				}

				break;

			case 'DAILY' :

				// Get date values
				list( $Y, $m, $d ) = $this->date_explode( 'Y-m-d', '-', $this->current_date );

				$day_start = $this->mktime( 0, 0, 0, $m, $d, $Y );

				// Bail if out of range
				if ( $day_start > $this->before ) {
					return true;
				}

				break;

			case 'HOURLY' :

				// Get date values
				list( $Y, $m, $d, $H ) = $this->date_explode( 'Y-m-d-H', '-', $this->current_date );

				$hour_start = $this->mktime( $H, 0, 0, $m, $d, $Y );

				// Bail if out of range
				if ( $hour_start > $this->before ) {
					return true;
				}

				break;

			case 'MINUTELY' :

				// Get date values
				list( $Y, $m, $d, $H, $i ) = $this->date_explode( 'Y-m-d-H-i', '-', $this->current_date );

				$minute_start = $this->mktime( $H, $i, 0, $m, $d, $Y );

				// Bail if out of range
				if ( $minute_start > $this->before ) {
					return true;
				}

				break;

			case 'SECONDLY' :

				// Bail if out of range
				if ( $this->current_date > $this->before ) {
					return true;
				}

				break;
		}

		// Return
		return false;
	}

	/**
	 * Used when iterating, this method checks if an Event is not in range.
	 *
	 * @return bool
	 */
	protected function not_in_range() {

		// Bail if no after
		if ( is_null( $this->after ) ) {
			return false;
		}

		// End might be in range, so use it
		$end_date_time = $this->current_date + $this->duration_time;

		// Bail if no expansions - check current date only
		if ( empty( $this->expansion_count ) && ( $end_date_time < $this->after ) ) {
			return true;
		}

		// Check range of possible expansions
		switch ( $this->freq ) {
			case 'YEARLY' :
				$year_details = $this->get_year_details( $this->date( 'Y', $end_date_time ) );

				// Get end
				$end = ! empty( $this->byweekno ) && ( $year_details[ 'week_end' ] > $year_details[ 'end' ] )
					? $year_details[ 'week_end' ]
					: $year_details[ 'end' ];

				// Bail if not in range
				if ( $end < $this->after ) {
					return true;
				}

				break;

			case 'MONTHLY' :

				// Get date values
				list( $Y, $m ) = $this->date_explode( 'Y-m', '-', $end_date_time );

				// Get month details
				$month_details = $this->get_month_details( $m, $Y );

				// Bail if not in range
				if ( $month_details[ 'end' ] < $this->after ) {
					return true;
				}

				break;

			case 'WEEKLY' :

				// Get date values
				list( $Y, $z ) = $this->date_explode( 'Y-z', '-', $end_date_time );

				// Get week details
				$week_details = $this->get_week_details( $z, $Y );

				// Bail if not in range
				if ( $week_details[ 'end' ] < $this->after ) {
					return true;
				}

				break;

			case 'DAILY' :

				// Get date values
				list( $Y, $m, $d ) = $this->date_explode( 'Y-m-d', '-', $end_date_time );

				$day_end = $this->mktime( 23, 59, 59, $m, $d, $Y );

				// Bail if not in range
				if ( $day_end < $this->after ) {
					return true;
				}

				break;

			case 'HOURLY' :

				// Get date values
				list( $Y, $m, $d, $H ) = $this->date_explode( 'Y-m-d-H', '-', $end_date_time );

				$hour_end = $this->mktime( $H, 59, 59, $m, $d, $Y );

				// Bail if not in range
				if ( $hour_end < $this->after ) {
					return true;
				}

				break;

			case 'MINUTELY' :

				// Get date values
				list( $Y, $m, $d, $H, $i ) = $this->date_explode( 'Y-m-d-H-i', '-', $end_date_time );

				$minute_end = $this->mktime( $H, $i, 59, $m, $d, $Y );

				// Bail if not in range
				if ( $minute_end < $this->after ) {
					return true;
				}

				break;

			case 'SECONDLY' :

				// Bail if not in range
				if ( $end_date_time < $this->after ) {
					return true;
				}

				break;
		}

		// Return
		return false;
	}

	/**
	 * Merge two sets of arrays.
	 *
	 * @param array $expanded_dates
	 * @param array $set
	 */
	protected function merge_set( & $expanded_dates = array(), $set = array() ) {

		// Bail if nothing to merge
		if ( empty( $set ) ) {
			$expanded_dates = array();
			return;
		}

		// Get the first set to merge
		$merge = $set[ 0 ];

		// Loop through and get the intersections
		for ( $i = 1; $i < count( $set ); $i++ ) {
			$merge = array_intersect( $merge, $set[ $i ] );
		}

		// Merge sets
		$expanded_dates = array_merge( $expanded_dates, $merge );
	}

	/** Expanders *************************************************************/

	/**
	 * Expand BYMONTH rule.
	 *
	 * @param int $date
	 * @return array
	 */
	protected function expand_bymonth( $date = 0 ) {

		// Default return value
		$dates = array();

		// Get date values
		list( $Y, $d, $H, $i, $s, $L ) = $this->date_explode( 'Y-d-H-i-s-L', '-', $date );

		// Last day for each month
		$limit = array( '', 31, $L
			? 29
			: 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );

		// Shifting allowed if day part is defined by rule
		if ( ! empty( $this->byweekno ) || ! empty( $this->byyearday ) || ! empty( $this->bymonthday ) || ! empty( $this->byday ) ) {
			$shift = true;
		}

		// Create one date for each MONTH
		foreach ( $this->bymonth as $month ) {

			// Out of range
			if ( $d > $limit[ $month ] ) {

				// Shift day
				if ( ! empty( $shift ) ) {
					$d = $limit[ $month ];

				// Skip month
				} else {
					continue;
				}
			}

			// Add to dates
			$dates[] = $this->mktime( $H, $i, $s, $month, $d, $Y );
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	/**
	 * Expand BYWEEKNO rule.
	 *
	 * @param int $date
	 * @return array
	 */
	protected function expand_byweekno( $date = 0 ) {

		// Default return value
		$dates = array();

		// Get date values
		list( $Y, $m, $H, $i, $s ) = $this->date_explode( 'Y-m-H-i-s', '-', $date );

		// Results are limited to current year
		$year[ $Y ] = $this->get_year_details( $Y );
		$start      = $year[ $Y ][ 'start' ];
		$end        = $year[ $Y ][ 'end' ];

		// Previous applied BYMONTH limits the result
		if ( ! empty( $this->bymonth ) ) {
			$start = $this->mktime( 0, 0, 0, $m,     1, $Y );
			$end   = $this->mktime( 0, 0, 0, $m + 1, 1, $Y ) - 1;
		}

		// Check results from overlapping years too
		if ( $year[ $Y ][ 'week_end' ] < $end )	{
			$year[ $Y + 1 ] = $this->get_year_details( $Y + 1 );

		} elseif ( $year[ $Y ][ 'week_start' ] > $start ) {
			$year[ $Y - 1 ] = $this->get_year_details( $Y - 1 );
		}

		// Take weekday from dtstart if day part NOT defined by rule
		if ( empty( $this->byyearday ) && empty( $this->bymonthday ) && empty( $this->byday ) ) {
			$dow     = $this->date( 'w', $this->dtstart );
			$weekday = self::DAYS[ $dow ];

			// Position of the weekday (depends on wkst)
			$pos_weekday = array_search( $weekday, $this->wkst_seq, true );
		}

		// Loop through years
		foreach ( $year as $Y => $year_details ) {

			// Create one date for each WEEK
			foreach ( $this->byweekno as $week ) {

				// Skip week if week number out of range
				if ( abs( $week ) > $year_details[ 'number_of_weeks' ] ) {
					continue;
				}

				if ( $week < 0 ) {
					$week = $year_details[ 'number_of_weeks' ] + $week + 1;
				}

				// Check weekday, only if weekday is specified by dtstart
				if ( isset( $pos_weekday ) ) {
					$test_date = $this->mktime( $H, $i, $s, 1, 1 + $year_details[ 'week_offset' ] + $pos_weekday + ( $week - 1 ) * 7, $Y );

					// Skip week if date out of range
					if ( $test_date < $start ) {
						continue;
					}

					// Skip week if date out of range
					if ( $test_date > $end ) {
						continue;
					}

				// Weekday is expanded later, find one valid day of the week
				} else {
					$pos = 0;

					do {

						// Skip week if position out of range
						if ( $pos > 6 ) {
							continue 2;
						}

						$test_date = $this->mktime( $H, $i, $s, 1, 1 + $year_details[ 'week_offset' ] + $pos + ( $week - 1 ) * 7, $Y );

						if ( ( $test_date >= $start ) && ( $test_date <= $end ) ) {
							break;
						}

						$pos++;

					} while ( 1 );
				}

				// Add to dates
				$dates[] = $test_date;
			}
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	/**
	 * Expand BYYEARDAY rule.
	 *
	 * @param int $date
	 * @return array
	 */
	protected function expand_byyearday( $date = 0 ) {

		// Default return value
		$dates = array();

		// Get date values
		list( $Y, $m, $z, $H, $i, $s, $L ) = $this->date_explode( 'Y-m-z-H-i-s-L', '-', $date );

		// Get number of year days
		$number_of_days = $this->year_days( $L );

		// Results are limited to current year
		$start = $this->mktime( 0,  0,  0,  1,  1,  $Y );
		$end   = $this->mktime( 23, 59, 59, 12, 31, $Y );

		// Previous applied BYMONTH limits the result
		if ( ! empty( $this->bymonth ) ) {

			// Get month details
			$month_details = $this->get_month_details( $m, $Y );

			// Get start & end
			$start = $month_details[ 'start' ];
			$end   = $month_details[ 'end' ];
		}

		// Previous applied BYWEEKNO limits the result
		if ( ! empty( $this->byweekno ) ) {

			// Get week details
			$week_details = $this->get_week_details( $z, $Y );

			if ( $start < $week_details[ 'start' ] ) {
				$start = $week_details[ 'start' ];
			}

			if ( $end > $week_details[ 'end' ] ) {
				$end = $week_details[ 'end' ];
			}
		}

		// Create one date for each YEARDAY
		foreach ( $this->byyearday as $day ) {

			if ( $day < 0 ) {
				$day = $number_of_days + $day + 1;
			}

			// Get timestamp
			$date = $this->mktime( $H, $i, $s, 1, $day, $Y );

			// Skip day if date out of range
			if ( $date < $start ) {
				continue;
			}

			// Skip day if date out of range
			if ( $date > $end ) {
				continue;
			}

			// Add to dates
			$dates[] = $date;
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	/**
	 * Expand BYMONTHDAY rule.
	 *
	 * @param int $date
	 * @return array
	 */
	protected function expand_bymonthday( $date = 0 ) {

		// Default return value
		$dates = array();

		// Get date values
		list( $Y, $m, $z, $H, $i, $s ) = $this->date_explode( 'Y-m-z-H-i-s', '-', $date );

		// Get month details
		$month[ $m ] = $this->get_month_details( $m, $Y );

		// Results are limited to current year
		$start = $this->mktime( 0,  0,  0,  1,  1,  $Y );
		$end   = $this->mktime( 23, 59, 59, 12, 31, $Y );

		// Previous applied BYMONTH limits the result
		if ( ! empty( $this->bymonth ) ) {
			$start = $month[ $m ][ 'start' ];
			$end   = $month[ $m ][ 'end' ];
		}

		// Previous applied BYWEEKNO limits the result
		if ( ! empty( $this->byweekno ) ) {

			// Get week details
			$week_details = $this->get_week_details( $z, $Y );

			if ( $start < $week_details[ 'start' ] ) {
				$start = $week_details[ 'start' ];
			}

			if ( $end > $week_details[ 'end' ] ) {
				$end = $week_details[ 'end' ];
			}

			// If no BYMONTH specified, consider overlapping months too
			if ( empty( $this->bymonth ) ) {

				// Get next month details
				if ( $month[ $m ][ 'end' ] < $end ) {
					$month[ $m + 1 ] = $this->get_month_details( $m + 1, $Y );

				// Get previous month details
				} elseif ( $month[ $m ][ 'start' ] > $start ) {
					$month[ $m - 1 ] = $this->get_month_details( $m - 1, $Y );
				}
			}
		}

		// Loop through months
		foreach ( $month as $m => $month_details ) {

			// Create one date for each MONTHDAY
			foreach ( $this->bymonthday as $day ) {

				// Skip day if day out of range
				if ( abs( $day ) > $month_details[ 'number_of_days' ] ) {
					continue;
				}

				if ( $day < 0 ) {
					$day = $month_details[ 'number_of_days' ] + $day + 1;
				}

				// Get timestamp
				$date = $this->mktime( $H, $i, $s, $m, $day, $Y );

				// Skip day if day out of range
				if ( $date < $start ) {
					continue;
				}

				// Skip day if day out of range
				if ( $date > $end ) {
					continue;
				}

				// Add to dates
				$dates[] = $date;
			}
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	/**
	 * Expand BYDAY rule.
	 *
	 * @param int $date
	 * @return array
	 */
	protected function expand_byday( $date = 0 ) {

		// Default return value
		$dates = array();

		// Special expand for WEEKLY
		if ( ! empty( $this->byweekno ) || ( 'WEEKLY' === $this->freq ) ) {

			// Get date values
			list( $Y, $m, $z, $H, $i, $s ) = $this->date_explode( 'Y-m-z-H-i-s', '-', $date );

			// Previous applied BYMONTH limits the result
			if ( ! empty( $this->bymonth ) ) {

				// Get month details
				$month_details = $this->get_month_details( $m, $Y );

				// Get start & end
				$start = $month_details[ 'start' ];
				$end   = $month_details[ 'end' ];
			}

			// Get week details
			$week_details = $this->get_week_details( $z, $Y );

			// Get date values
			list( $Y, $m, $d ) = $this->date_explode( 'Y-m-d', '-', $week_details[ 'start' ] );

			// Apply BYDAY
			foreach ( $this->byday as $option ) {

				// Skip day if position & WEEKLY is invalid
				if ( ! empty( $option[ 'pos' ] ) ) {
					continue;
				}

				// Position of day from week start
				$pos  = array_search( $option[ 'weekday' ], $this->wkst_seq, true );

				// Get timestamp
				$date = $this->mktime( $H, $i, $s, $m, $d + $pos, $Y );

				// Skip day if start out of range
				if ( isset( $start ) && ( $date < $start ) ) {
					continue;
				}

				// Skip day if end out of range
				if ( isset( $end ) && ( $date > $end ) ) {
					continue;
				}

				// Add to dates
				$dates[] = $date;
			}

			// Sort dates numerically
			sort( $dates, SORT_NUMERIC );

			// Return sorted dates
			return $dates;
		}

		// Special expand for MONTHLY
		if ( ! empty( $this->bymonth ) || ( 'MONTHLY' === $this->freq ) ) {

			// Get date values
			list( $Y, $m, $H, $i, $s ) = $this->date_explode( 'Y-m-H-i-s', '-', $date );

			// Get month details
			$month_details = $this->get_month_details( $m, $Y );

			// Apply BYDAY
			foreach ( $this->byday as $option ) {

				// Position of day from month start
				$pos      = array_search( $option[ 'weekday' ], $month_details[ 'start_seq' ] );
				$all_days = array();

				while ( $pos < $month_details[ 'number_of_days' ] ) {
					$all_days[] = $this->mktime( $H, $i, $s, $m, $pos + 1, $Y );
					$pos += 7;
				}

				// Skip if not all day
				if ( empty( $all_days ) ) {
					continue;
				}

				// No position - merge all days and skip
				if ( empty( $option[ 'pos' ] ) ) {
					$dates = array_merge( $dates, $all_days );
					continue;
				}

				// Skip if position out of range
				if ( abs( $option[ 'pos' ] ) > count( $all_days ) ) {
					continue;
				}

				// Merge day by position
				$dates[] = ( $option[ 'pos' ] < 0 )
					? $all_days[ count( $all_days ) + $option[ 'pos' ] ]
					: $all_days[ $option[ 'pos' ] - 1 ];
			}

			// Sort dates numerically
			sort( $dates, SORT_NUMERIC );

			// Return sorted dates
			return $dates;
		}

		// Special expand for YEARLY
		if ( 'YEARLY' === $this->freq ) {

			// Get date values
			list( $Y, $H, $i, $s ) = $this->date_explode( 'Y-H-i-s', '-', $date );

			// Get year details
			$year_details = $this->get_year_details( $Y );

			// Apply BYDAY
			foreach ( $this->byday as $option ) {

				// Position of day from year start
				$pos      = array_search( $option[ 'weekday' ], $year_details[ 'start_seq' ] );
				$all_days = array();

				while ( $pos < $year_details[ 'number_of_days' ] ) {
					$all_days[] = $this->mktime( $H, $i, $s, 1, $pos + 1, $Y );
					$pos += 7;
				}

				// Skip if not all day
				if ( empty( $all_days ) ) {
					continue;
				}

				// No position - merge all days and skip
				if ( empty( $option[ 'pos' ] ) ) {
					$dates = array_merge( $dates, $all_days );
					continue;
				}

				// Skip if position out of range
				if ( abs( $option[ 'pos' ] ) > count( $all_days ) ) {
					continue;
				}

				// Merge day by position
				$dates[] = ( $option[ 'pos' ] < 0 )
					? $all_days[ count( $all_days ) + $option[ 'pos' ] ]
					: $all_days[ $option[ 'pos' ] - 1 ];
			}

			// Sort dates numerically
			sort( $dates, SORT_NUMERIC );

			// Return sorted dates
			return $dates;
		}
	}

	/**
	 * Expand BYHOUR rule.
	 *
	 * @param int $date
	 * @return array
	 */
	protected function expand_byhour( $date = 0 ) {

		// Default return value
		$dates = array();

		// Get date values
		list( $Y, $m, $d, $i, $s ) = $this->date_explode( 'Y-m-d-i-s', '-', $date );

		// Loop through hours
		foreach ( $this->byhour as $hour ) {
			$dates[] = $this->mktime( $hour, $i, $s, $m, $d, $Y );
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	/**
	 * Expand BYMINUTE rule.
	 *
	 * @param int $date
	 * @return array
	 */
	protected function expand_byminute( $date = 0 ) {

		// Default return value
		$dates = array();

		// Get date values
		list( $Y, $m, $d, $H, $s ) = $this->date_explode( 'Y-m-d-H-s', '-', $date );

		// Loop through minutes
		foreach ( $this->byminute as $minute ) {
			$dates[] = $this->mktime( $H, $minute, $s, $m, $d, $Y );
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	/**
	 * Expand BYSECOND rule.
	 *
	 * @param int $date
	 * @return array
	 */
	protected function expand_bysecond( $date = 0 ) {

		// Default return value
		$dates = array();

		// Get date values
		list( $Y, $m, $d, $H, $i ) = $this->date_explode( 'Y-m-d-H-i', '-', $date );

		// Loop through seconds
		foreach ( $this->bysecond as $second ) {
			$dates[] = $this->mktime( $H, $i, $second, $m, $d, $Y );
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	/** Limits ****************************************************************/

	/**
	 * Limit BYMONTH rule.
	 *
	 * @param int $timestamp
	 * @return bool
	 */
	protected function limit_bymonth( $timestamp = 0 ) {

		// Get vars
		$needle   = (int) $this->date( 'n', $timestamp );
		$haystack = array_map( 'intval', $this->bymonth );

		// Check
		return in_array( $needle, $haystack, true );
	}

	/**
	 * Limit BYYEARDAY rule.
	 *
	 * @param int $timestamp
	 * @return bool
	 */
	protected function limit_byyearday( $timestamp = 0 ) {

		// Get date values
		list( $z, $L ) = $this->date_explode( 'z-L', '-', $timestamp );

		// Cast to int
		$haystack = array_map( 'intval', $this->byyearday );

		// Return if matched
		if ( in_array( $z + 1, $haystack, true ) ) {
			return true;
		}

		// Get number of year days
		$number_of_days = $this->year_days( $L );

		// Return if matched
		if ( in_array( $z - $number_of_days, $haystack, true ) ) {
			return true;
		}

		// Return
		return false;
	}

	/**
	 * Limit BYMONTHDAY rule.
	 *
	 * @param int $timestamp
	 * @return bool
	 */
	protected function limit_bymonthday( $timestamp = 0 ) {

		// Get date values
		list( $j, $t ) = $this->date_explode( 'j-t', '-', $timestamp );

		// Cast to int
		$haystack = array_map( 'intval', $this->bymonthday );

		// Return if matched
		if ( in_array( $j, $haystack, true ) ) {
			return true;
		}

		// Return if matched
		if ( in_array( $j - $t - 1, $haystack, true ) ) {
			return true;
		}

		// Return
		return false;
	}

	/**
	 * Limit BYDAY rule.
	 *
	 * @param int $timestamp
	 * @return bool
	 */
	protected function limit_byday( $timestamp = 0 ) {

		// Recombine position & weekday
		foreach ( $this->byday as $option )	{
			$byday[] = $option[ 'pos' ] . $option[ 'weekday' ];
		}

		// Get date values
		list( $w, $j, $z, $t, $L ) = $this->date_explode( 'w-j-z-t-L', '-', $timestamp );

		// Check weekday without position
		if ( in_array( self::DAYS[ $w ], $byday, true ) ) {
			return true;
		}

		// Previous applied BYMONTH - check position relative to this month
		if ( ! empty( $this->bymonth ) ) {
			$try = ceil( $j / 7 );

			// Return if matched
			if ( in_array( $try . self::DAYS[ $w ], $byday, true ) ) {
				return true;
			}

			$try = ceil( ( $t - $j + 1 ) / 7 ) * -1;

			// Return if matched
			if ( in_array( $try . self::DAYS[ $w ], $byday, true ) ) {
				return true;
			}

		// Check position relative to year
		} else {

			// Get number of year days
			$number_of_days = $this->year_days( $L );

			$try = ceil( ( $z + 1 ) / 7 );

			// Return if matched
			if ( in_array( $try . self::DAYS[ $w ], $byday, true ) ) {
				return true;
			}

			$try = ceil( ( $number_of_days - $z ) / 7 ) * -1;

			// Return if matched
			if ( in_array( $try . self::DAYS[ $w ], $byday, true ) ) {
				return true;
			}
		}

		// Return
		return false;
	}

	/**
	 * Limit BYHOUR rule.
	 *
	 * @param int $timestamp
	 * @return bool
	 */
	protected function limit_byhour( $timestamp = 0 ) {

		// Get vars
		$needle   = (int) $this->date( 'G', $timestamp );
		$haystack = array_map( 'intval', $this->byhour );

		// Check
		return in_array( $needle, $haystack, true );
	}

	/**
	 * Limit BYMINUTE rule.
	 *
	 * @param int $timestamp
	 * @return bool
	 */
	protected function limit_byminute( $timestamp = 0 ) {

		// Get vars
		$needle   = (int) $this->date( 'i', $timestamp );
		$haystack = array_map( 'intval', $this->byminute );

		// Check
		return in_array( $needle, $haystack, true );
	}

	/**
	 * Limit BYSETPOS rule.
	 *
	 * @param array $limited_dates
	 * @return array
	 */
	protected function limit_bysetpos( $limited_dates = array() ) {

		// Default return value.
		$retval = [];

		// Get date count.
		$num = count( $limited_dates );

		// Loop through positions.
		foreach ( $this->bysetpos as $pos ) {

			// Reposition if negative...
			if ( $pos < 0 ) {
				$pos = $num + $pos + 1;
			}

			// Add limited date to return value.
			if ( isset( $limited_dates[ $pos - 1 ] ) ) {
				$retval[] = $limited_dates[ $pos - 1 ];
			}
		}

		// Return.
		return $retval;
	}

	/** Details ***************************************************************/

	/**
	 * Get some details for a specific week, given a day and year.
	 *
	 * These details are internally cached to avoid rework.
	 *
	 * @param int $day
	 * @param int $year
	 * @return array
	 */
	protected function get_week_details( $day = 0, $year = 0 ) {

		// Return if already cached
		if ( ! empty( $this->cached_details[ 'week' ][ $year ][ $day ] ) ) {
			return $this->cached_details[ 'week' ][ $year ][ $day ];
		}

		// Get year details
		$year_details = $this->get_year_details( $year );

		// Difference to 1st week in year
		$week_diff = floor( ( $day - $year_details[ 'week_offset' ] ) / 7 );

		// Get start of week
		$start = $this->mktime( 0,  0,  0,  1, 1 + $year_details[ 'week_offset' ] + $week_diff * 7,     $year );

		// Get end of week
		$end   = $this->mktime( 23, 59, 59, 1, 1 + $year_details[ 'week_offset' ] + $week_diff * 7 + 6, $year );

		// Setup the return value
		$retval = $this->cached_details[ 'week' ][ $year ][ $day ] = array(
			'start' => $start,
			'end'   => $end
		);

		// Return
		return $retval;
	}

	/**
	 * Get some details for a specific month, given a month and year.
	 *
	 * These details are internally cached to avoid rework.
	 *
	 * @param int $month
	 * @param int $year
	 * @return array
	 */
	protected function get_month_details( $month = 0, $year = 0 ) {

		// Return if already cached
		if ( ! empty( $this->cached_details[ 'month' ][ $year ][ $month ] ) ) {
			return $this->cached_details[ 'month' ][ $year ][ $month ];
		}

		// Get start of month
		$start = $this->mktime( 0, 0, 0, $month, 1, $year );

		// Get date values
		list( $number_of_days, $w ) = $this->date_explode( 't-w', '-', $start );

		// Get end of month
		$end = $this->mktime( 23, 59, 59, $month, $number_of_days, $year );

		// Weekday order at month start / end
		$start_seq = $this->weekday_order( $w );

		// Setup the return value
		$retval = $this->cached_details[ 'month' ][ $year ][ $month ] = array(
			'start'          => $start,
			'end'            => $end,
			'start_seq'      => $start_seq,
			'number_of_days' => $number_of_days
		);

		// Return
		return $retval;
	}

	/**
	 * Get some details for a specific year, given year.
	 *
	 * These details are internally cached to avoid rework.
	 *
	 * @param int $month
	 * @param int $year
	 * @return array
	 */
	protected function get_year_details( $year = 0 ) {

		// Return if already cached
		if ( ! empty( $this->cached_details[ 'year' ][ $year ] ) ) {
			return $this->cached_details[ 'year' ][ $year ];
		}

		// Get start of year
		$start = $this->mktime( 0,  0,  0,  1,  1,  $year );

		// Get end of year
		$end   = $this->mktime( 23, 59, 59, 12, 31, $year );

		// Get date values
		list( $L, $w ) = $this->date_explode( 'L-w', '-', $start );

		// Get number of year days
		$number_of_days = $this->year_days( $L );

		// Weekday order at year start / end
		$start_seq = $this->weekday_order( $w );
		$end_seq   = $this->weekday_order( $this->date( 'w', $end ) );

		// Get start week position
		$start_pos = array_search( $this->wkst, $start_seq );

		// Week offset (negative = 1st week begins december)
		$week_offset = ( $start_pos < 4 )
			? $start_pos
			: $start_pos - 7;

		// Get end week position
		$end_pos = array_search( $this->wkst, $end_seq );

		// Last week offset (negative = last week ends january)
		$last_week_offset = ( $end_pos < 4 )
			? 0 - $end_pos
			: 7 - $end_pos;

		// Number of calendar weeks
		$number_of_weeks = round( ( $number_of_days - $week_offset - $last_week_offset ) / 7 );

		// Start of first week
		$week_start = $this->mktime( 0, 0, 0, 1, 1 + $week_offset, $year );

		// End of last week
		$week_end = $this->mktime( 23, 59, 59, 12, 31 - $last_week_offset, $year );

		// Setup the return value
		$retval = $this->cached_details[ 'year' ][ $year ] = array(
			'start'           => $start,
			'end'             => $end,
			'start_seq'       => $start_seq,
			'number_of_days'  => $number_of_days,
			'number_of_weeks' => $number_of_weeks,
			'week_offset'     => $week_offset,
			'week_start'      => $week_start,
			'week_end'        => $week_end
		);

		// Return
		return $retval;
	}

	/** Helpers ***************************************************************/

	/**
	 * DateTime and DateTimeZone aware wrapper for mktime().
	 *
	 * @param int $hour
	 * @param int $min
	 * @param int $sec
	 * @param int $mon
	 * @param int $day
	 * @param int $year
	 * @return mixed
	 */
	protected function mktime( $hour, $min, $sec, $mon, $day, $year ) {

		// Create DateTime
		if ( ! $this->datetime instanceof \DateTime ) {
			$this->datetime = new \DateTime( null, $this->timezone );
		}

		// Try to set the date and time
		try {
			$this->datetime->setTime( $hour, $min, $sec );
			$this->datetime->setDate( $year, $mon, $day );

		// Bail if error
		} catch ( \Exception $e ) {
			return false;
		}

		// Return unix timestamp
		return $this->datetime->format( 'U' );
	}

	/**
	 * DateTime and DateTimeZone aware wrapper for strtotime().
	 *
	 * @param string $datetime
	 * @return mixed
	 */
	protected function strtotime( $datetime = '' ) {

		// Try to get the DateTime
		try {
			$this->datetime = new \DateTime( $datetime, $this->timezone );

		// Bail if error
		} catch ( \Exception $e ) {
			return false;
		}

		// Return unix timestamp
		return $this->datetime->format( 'U' );
	}

	/**
	 * DateTime and DateTimeZone aware wrapper for date().
	 *
	 * @param string $format
	 * @param int    $timestamp
	 * @param mixed  $add
	 * @return mixed
	 */
	protected function date( $format = '', $timestamp = 0, $add = null ) {

		// Try to get the DateTime
		try {
			$this->datetime = new \DateTime( '@' . $timestamp );

		// Bail if error
		} catch ( \Exception $e ) {
			return false;
		}

		// Set the time zone
		$this->datetime->setTimezone( $this->timezone );

		// Maybe add
		if ( ! is_null( $add ) && ( $add instanceof \DateInterval ) ) {
			$this->datetime->add( $add );
		}

		// Return, formatted
		return $this->datetime->format( $format );
	}

	/**
	 * Explode a DateTime format by a delimeter, using the DateTime and
	 * DateTimeZone.
	 *
	 * @param string $format
	 * @param string $delimiter
	 * @param int    $timestamp
	 * @return array
	 */
	protected function date_explode( $format = '', $delimiter = '', $timestamp = 0 ) {
		return explode( $delimiter, $this->date( $format, $timestamp ) );
	}

	/** Private ***************************************************************/

	/**
	 * Get number of days in the year.
	 *
	 * @param int $leap
	 * @return int
	 */
	private function year_days( $leap = 0 ) {
		return ! empty( $leap )
			? 366
			: 365;
	}

	/**
	 * Lowercase all of the keys of an array.
	 *
	 * @param array $args
	 * @return array
	 */
	private function lc_keys( $args = array() ) {

		// Default return value
		$retval = array();

		// Loop through and
		foreach ( $args as $key => $value ) {
			$retval[ strtolower( $key ) ] = is_string( $value )
				? trim( $value )
				: $value;
		}

		// Return
		return $retval;
	}

	/**
	 * Get a week sequence.
	 *
	 * @param int $day_of_week
	 * @return array
	 */
	private function weekday_order( $day_of_week = 0 ) {

		// Get all week days
		$week = self::DAYS;

		// Day of week
		$day  = $week[ $day_of_week ];

		// Loop through days and shift them
		while ( $day !== $week[ 0 ] ) {
			$week[] = array_shift( $week );
		}

		// Return
		return $week;
	}

	/**
	 * Trims to prevent empties, and explodes.
	 *
	 * @param string $delimiter
	 * @param string $string
	 * @return array
	 */
	private function safe_explode( $delimiter = ',', $string = '' ) {
		return explode( $delimiter, rtrim( $string, $delimiter ) );
	}
}
