<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Event Object
 *
 * Standard Event for use within WP Event Calendar. This object does not contain
 * all of the Schema.org properties. It also contains RFC 2445 specific properties
 * which are identified in the link.
 *
 * @see http://schema.org/Event
 * @see https://tools.ietf.org/html/rfc5545
 */
class WP_Event_Calendar_Event {

	/**
	 * @var string Name of the item
	 *
	 * @link http://schema.org/name
	 */
	protected $name;

	/**
	 * @var string Description of the item
	 *
	 * @link http://schema.org/description
	 */
	protected $description;

	/**
	 * @var DateTime The start date and time in ISO 8601 date format
	 *
	 * @link http://schema.org/startDate
	 */
	protected $start_date;

	/**
	 * @var DateTime The end date and time in ISO 8601 date format
	 *
	 * @link http://schema.org/endDate
	 */
	protected $end_date;

	/**
	 * @var bool all day flag used to determine business logic
	 */
	private $all_day = false;

	/**
	 * @var string An event_status of an event represents it's state; useful if
	 *             cancelled or rescheduled
	 *
	 * @link http://schema.org/EventStatusType
	 */
	protected $event_status;

	/**
	 * @var array Schema.org Event statuses mapped to RFC5545 property
	 */
	private static $event_status_mapping = array(
		'EventScheduled'   => 'CONFIRMED',
		'EventCancelled'   => 'CANCELLED',
		'EventRescheduled' => 'CONFIRMED',
		'EventPostponed'   => 'TENTATIVE',
	);

	/**
	 * @var string Where the event is happening
	 *
	 * @link http://schema.org/location
	 */
	protected $location;

	/**
	 * WP_Event_Calendar_Event constructor handles validating the data and
	 * getting a handle on what the event is doing. This way we can echo the
	 * object and have it render an ICS file
	 *
	 * @todo Can I do PHP primitive type checks here or object checks?
	 * @todo Use PhysicalLocation to align with Schema.org - check if this is possible
	 *
	 * @param DateTime $start_date    Start date in ISO-8601 date format
	 * @param DateTime $end_date      End date in ISO-8601 date format. If NULL it will assume an all day event
	 * @param string   $name          Name of the event
	 * @param string   $description   Description of the event possibly with event details
	 * @param string   $location      Simple string of text describing the event location
	 * @param int      $repeat        Repetition of the event
	 * @param string   $event_status  Defaults to EventScheduled
	 */
	public function __construct( $start_date = null, $end_date = null, $name = '', $description = '', $location = '', $repeat = 0, $event_status = 'EventScheduled' ) {

		// Check if start_date is instance of DateTime object
		if ( $start_date instanceof DateTime ) {
			$this->start_date = $start_date;

		// Assumes we are at least passing some date through
		} elseif ( ! empty( $start_date ) ) {
			$this->start_date = new DateTime( $start_date );

		// Bail if event has no start_date
		} else {
			return;
		}

		// If the end_date is null, this is an all_day event
		if ( is_null( $end_date ) ) {
			$this->start_date->setTime( 0, 0, 0 ); // Set to beginning of day (all day event)
			$this->end_date = clone $this->start_date;
			$this->end_date->setTime( 23, 59, 59 ); // EOD
			$this->all_day = true;

		// End date exists
		} elseif ( $end_date instanceof DateTime ) {
			$this->end_date = $end_date;

		// End dates are not required - if empty we want to skip added business logic
		} elseif ( empty( $end_date ) ) {
			$this->end_date = '';

		// Assumes we are passing some date through if getting here
		} else {
			$this->end_date = new DateTime( $end_date );
		}

		// Not doing anything with the rest of the properties for now
		$this->name         = $name;
		$this->description  = $description;
		$this->event_status = $event_status;
		$this->location     = $location;
		$this->repeat       = $repeat;
	}

	/**
	 * This converts the Schema.org enumeration to the RFC spec
	 *
	 * @see https://tools.ietf.org/html/rfc5545#section-3.8.1.11
	 * @see http://schema.org/EventStatusType
	 *
	 * @return string Event Status according to RFC5545
	 */
	protected function event_status_to_rfc() {

		// Make sure the EventStatus is to spec
		if ( ! empty( $this->event_status_mapping[ $this->event_status ] ) ) {
			return $this->event_status_mapping[ $this->event_status ];
		}

		// Returning EventScheduled/CONFIRMED if spec does not match
		return $this->event_status_mapping[ 'EventScheduled' ];
	}

	/**
	 * Formats the event date according to how the RFC specifies
	 *
	 * @see https://tools.ietf.org/html/rfc5545#section-3.3.5
	 * @param $date DateTime Date to be formatted
	 *
	 * @return string date
	 */
	protected function event_date_to_rfc( $date = '' ) {

		// @todo check WordPress UTC functions
		// @todo look at moving this conversion towards the constructor
		if ( empty( $date ) || ! ( $date instanceof DateTime ) ) {
			return '';
		}

		// 20160119T070000 - not sure about returning Z
		return $date->format( 'Ymd\THis' );
	}

	/**
	 * Returns the event recurrence in accordance with the RFC
	 *
	 * @see https://tools.ietf.org/html/rfc5545#section-3.3.10
	 *
	 * @return string Frequency of repeated event
	 */
	protected function event_recurrance_to_rfc() {

		// No recurrance by default
		$frequency = '';

		// Check how our event repeats
		switch ( $this->repeat ) {
			case 10:
				$frequency = 'FREQ=WEEKLY';
				break;
			case 100:
				$frequency = 'FREQ=MONTHLY';
				break;
			case 1000:
				$frequency = 'FREQ=YEARLY';
				break;
		}

		// Get the UNTIL portion if the frequency and end_date are not empty
		if ( ! empty( $frequency ) && ! empty( $this->end_date ) ) {
			$frequency .= ';UNTIL=' . $this->event_date_to_rfc( $this->end_date );
		}

		return $frequency;
	}

	/**
	 * All of the array keys are VEVENT properties as part of the RFC. If
	 * the property is empty() it will just be ignored in the end. They are
	 * here to be filled in eventually and as a reminder that we can expand
	 * our implementation of the spec.
	 *
	 * Note: Keeping them in the order of the RFC spec for now
	 *
	 * @return array Array of key/value pairs to match the RFC spec
	 */
	protected function event_to_rfc() {
		return array(

			// Required
			'dtstamp'     => '', // required if using 'METHOD' - which we aren't
			'dtstart'     => $this->event_date_to_rfc( $this->start_date ), // the only real required property

			// Optional
			'class'       => '',
			'created'     => '',
			'description' => $this->description,
			'geo'         => '',
			'last-mod'    => '',
			'location'    => $this->location,
			'organizer'   => '',
			'priority'    => '',
			'seq'         => '',
			'status'      => $this->event_status_to_rfc(),
			'summary'     => $this->name, // title of the event?
			'transp'      => '',
			'url'         => '',
			'recurid'     => '',
			'rrule'       => $this->event_recurrance_to_rfc(),
			'dtend'       => $this->event_date_to_rfc( $this->end_date ),
			'duration'    => '',
			'attach'      => '',
			'attendee'    => '',
			'categories'  => '',
			'comment'     => '',
			'contact'     => '',
			'exdate'      => '',
			'rstatus'     => '',
			'related'     => '',
			'resources'   => '',
			'rdate'       => '',
			'x-prop'      => '',
			'iana-prop'   => ''
		);
	}

	/**
	 * The object will return an ICS VEVENT for use in an iCal. This formats the
	 * object according to RFC5545.
	 *
	 * @see https://tools.ietf.org/html/rfc5545#section-3.6.1
	 *
	 * @return string ICS Formatted VEVENT
	 */
	public function __toString() {

		// Get the event as an array of keys & values to match the RFC
		$vevent = $this->event_to_rfc();

		// Start going through the array and building the event
		$event = 'BEGIN:VEVENT' . PHP_EOL;

		foreach ( $vevent as $key => $value ) {
			if ( ! empty( $value ) ) {
				$event .= strtoupper( $key ) . ':' . $value . PHP_EOL;
			}
		}

		$event .= 'END:VEVENT' . PHP_EOL;

		return apply_filters( 'wp_event_calendar_event', $event, $vevent );
	}
}
