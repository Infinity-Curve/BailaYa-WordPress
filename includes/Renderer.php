<?php
declare(strict_types=1);

namespace BailaYaWP;

use BailaYa\Dto\Instructor;
use BailaYa\Dto\PrivateLessonInstructor;
use BailaYa\Dto\StudioClass;
use BailaYa\Dto\StudioEvent;
use BailaYa\Dto\StudioLocation;
use BailaYa\Dto\StudioPackage;
use BailaYa\Dto\StudioProfile;
use BailaYa\Dto\UserProfile;

if (!defined('ABSPATH')) exit;

final class Renderer
{
    /**
     * Render a schedule list using a PHP template.
     *
     * @param list<StudioClass> $classes
     * @param array{
     *   locale?: string,
     *   labels?: array{ instructor?: string, viewOnMap?: string, online?: string },
     *   className?: string,
     *   itemClassName?: string,
     *   nameClassName?: string,
     *   detailsClassName?: string,
     *   locationClassName?: string,
     *   mapLinkClassName?: string,
     *   instructorClassName?: string,
     *   hideLocation?: bool
     * } $opts
     */
    public static function classSchedule(array $classes, array $opts = []): string
    {
        wp_enqueue_style('bailaya');

        $defaults = [
            'locale' => get_locale() ?: 'en',
            'labels' => [
                'instructor' => __('Instructor:', 'bailaya'),
                'viewOnMap' => __('View on map', 'bailaya'),
                'online' => __('Online', 'bailaya'),
            ],
            'className' => 'bailaya-list',
            'itemClassName' => 'bailaya-card',
            'nameClassName' => 'bailaya-name',
            'detailsClassName' => 'bailaya-details',
            'locationClassName' => 'bailaya-location',
            'mapLinkClassName' => 'bailaya-map-link',
            'instructorClassName' => 'bailaya-instructor',
            'hideLocation' => false,
        ];
        $data = array_replace_recursive($defaults, $opts);

        ob_start();
        $template = BAILAYA_WP_PATH . 'templates/class-schedule.php';
        /** @var list<StudioClass> $classes */
        include $template;
        return (string)ob_get_clean();
    }

    /**
     * Render an event list using a PHP template. Events are classes with no
     * studio type, and carry a `host` rather than an instructor.
     *
     * @param list<StudioEvent> $events
     * @param array{
     *   locale?: string,
     *   labels?: array{ host?: string, viewOnMap?: string, online?: string },
     *   className?: string,
     *   itemClassName?: string,
     *   nameClassName?: string,
     *   detailsClassName?: string,
     *   locationClassName?: string,
     *   mapLinkClassName?: string,
     *   hostClassName?: string,
     *   hideLocation?: bool
     * } $opts
     */
    public static function eventSchedule(array $events, array $opts = []): string
    {
        wp_enqueue_style('bailaya');

        $defaults = [
            'locale' => get_locale() ?: 'en',
            'labels' => [
                'host' => __('Host:', 'bailaya'),
                'viewOnMap' => __('View on map', 'bailaya'),
                'online' => __('Online', 'bailaya'),
            ],
            'className' => 'bailaya-list',
            'itemClassName' => 'bailaya-card',
            'nameClassName' => 'bailaya-name',
            'detailsClassName' => 'bailaya-details',
            'locationClassName' => 'bailaya-location',
            'mapLinkClassName' => 'bailaya-map-link',
            'hostClassName' => 'bailaya-instructor',
            'hideLocation' => false,
        ];
        $data = array_replace_recursive($defaults, $opts);

        ob_start();
        $template = BAILAYA_WP_PATH . 'templates/event-schedule.php';
        /** @var list<StudioEvent> $events */
        include $template;
        return (string)ob_get_clean();
    }

    /**
     * Render an instructor list via a template.
     *
     * @param list<Instructor> $instructors
     * @param array{
     *   className?: string,
     *   itemClassName?: string,
     *   imageWrapperClassName?: string,
     *   imageClassName?: string,
     *   bodyClassName?: string,
     *   nameClassName?: string,
     *   bioClassName?: string,
     * } $opts
     */
    public static function instructorList(array $instructors, array $opts = []): string
    {
        wp_enqueue_style('bailaya');

        $defaults = [
            'className' => 'bailaya-instructors mt-6 md:mt-12 flex flex-col space-y-8',
            'itemClassName' => 'bailaya-instructor-card flex flex-col md:flex-row items-center rounded-lg border border-[#DCDCDC] shadow-lg overflow-hidden',
            'imageWrapperClassName' => 'bailaya-instructor-imgwrap w-full p-4 pb-0 md:pb-4 md:w-1/3 aspect-square',
            'imageClassName' => 'bailaya-instructor-img w-full h-full rounded-xl object-cover',
            'bodyClassName' => 'bailaya-instructor-body p-6 flex-1 text-left',
            'nameClassName' => 'bailaya-instructor-name text-xl md:text-3xl font-semibold',
            'bioClassName' => 'bailaya-instructor-bio mt-2 text-sm md:text-base',
        ];
        $data = array_replace_recursive($defaults, $opts);

        ob_start();
        $template = BAILAYA_WP_PATH . 'templates/instructor-list.php';
        /** @var list<Instructor> $instructors */
        include $template;
        return (string) ob_get_clean();
    }

    /**
     * Render a grid of purchasable group-class packages.
     *
     * @param list<StudioPackage> $packages
     * @param array{
     *   locale?: string,
     *   hideValidity?: bool,
     *   buyBaseUrl?: string,
     *   className?: string,
     *   itemClassName?: string,
     *   nameClassName?: string,
     *   descriptionClassName?: string,
     *   metaClassName?: string,
     *   typesClassName?: string,
     *   priceClassName?: string,
     *   btnClassName?: string,
     *   labels?: array{
     *     buy?: string,
     *     classes?: string,
     *     validFor?: string,
     *     month?: string,
     *     months?: string
     *   }
     * } $opts
     */
    public static function packageList(array $packages, array $opts = []): string
    {
        wp_enqueue_style('bailaya');

        $defaults = [
            'locale'             => get_locale() ?: 'en',
            'hideValidity'       => false,
            'buyBaseUrl'         => 'https://www.bailaya.com/packages/',
            'className'          => 'bailaya-pkg-grid',
            'itemClassName'      => 'bailaya-pkg-card',
            'nameClassName'      => 'bailaya-pkg-name',
            'descriptionClassName' => 'bailaya-pkg-desc',
            'metaClassName'      => 'bailaya-pkg-meta',
            'typesClassName'     => 'bailaya-pkg-types',
            'priceClassName'     => 'bailaya-pkg-price',
            'btnClassName'       => 'bailaya-pl-btn',
            'labels'             => [
                'buy'     => __('Buy Now', 'bailaya'),
                'classes' => __('classes', 'bailaya'),
                'validFor'=> __('Valid for', 'bailaya'),
                'month'   => __('month', 'bailaya'),
                'months'  => __('months', 'bailaya'),
            ],
        ];
        $data = array_replace_recursive($defaults, $opts);

        ob_start();
        $template = BAILAYA_WP_PATH . 'templates/package-list.php';
        /** @var list<StudioPackage> $packages */
        include $template;
        return (string) ob_get_clean();
    }

    /**
     * Render a list of private lesson instructors with availability and pricing.
     *
     * @param list<PrivateLessonInstructor> $instructors
     * @param array{
     *   locale?: string,
     *   bookBaseUrl?: string,
     *   className?: string,
     *   itemClassName?: string,
     *   imageWrapperClassName?: string,
     *   imageClassName?: string,
     *   bodyClassName?: string,
     *   nameClassName?: string,
     *   bioClassName?: string,
     *   sectionClassName?: string,
     *   sectionHeadingClassName?: string,
     *   slotClassName?: string,
     *   pricingEntryClassName?: string,
     *   btnClassName?: string,
     *   labels?: array{
     *     availability?: string,
     *     pricing?: string,
     *     book?: string,
     *     minutes?: string
     *   }
     * } $opts
     */
    public static function privateLessonInstructors(array $instructors, array $opts = []): string
    {
        wp_enqueue_style('bailaya');

        $defaults = [
            'locale'                 => get_locale() ?: 'en',
            'bookBaseUrl'            => 'https://www.bailaya.com/en/book/private-lesson/',
            'className'              => 'bailaya-pl-list',
            'itemClassName'          => 'bailaya-pl-card',
            'imageWrapperClassName'  => 'bailaya-pl-imgwrap',
            'imageClassName'         => 'bailaya-pl-img',
            'bodyClassName'          => 'bailaya-pl-body',
            'nameClassName'          => 'bailaya-pl-name',
            'bioClassName'           => 'bailaya-pl-bio',
            'sectionClassName'       => 'bailaya-pl-section',
            'sectionHeadingClassName'=> 'bailaya-pl-section-heading',
            'slotClassName'          => 'bailaya-pl-slot',
            'pricingEntryClassName'  => 'bailaya-pl-pricing-entry',
            'btnClassName'           => 'bailaya-pl-btn',
            'labels'                 => [
                'availability' => __('Availability', 'bailaya'),
                'pricing'      => __('Pricing', 'bailaya'),
                'book'         => __('Book Now', 'bailaya'),
                'minutes'      => __('min', 'bailaya'),
            ],
        ];
        $data = array_replace_recursive($defaults, $opts);

        ob_start();
        $template = BAILAYA_WP_PATH . 'templates/private-lesson-instructors.php';
        /** @var list<PrivateLessonInstructor> $instructors */
        include $template;
        return (string) ob_get_clean();
    }

    /**
     * Render the studio's locations (primary first).
     *
     * @param list<StudioLocation> $locations
     * @param array{
     *   locale?: string,
     *   hidePrimaryBadge?: bool,
     *   hideDirections?: bool,
     *   className?: string,
     *   itemClassName?: string,
     *   nameClassName?: string,
     *   addressClassName?: string,
     *   badgeClassName?: string,
     *   linkClassName?: string,
     *   labels?: array{
     *     primary?: string,
     *     directions?: string
     *   }
     * } $opts
     */
    public static function locationList(array $locations, array $opts = []): string
    {
        wp_enqueue_style('bailaya');

        $defaults = [
            'locale'           => get_locale() ?: 'en',
            'hidePrimaryBadge' => false,
            'hideDirections'   => false,
            'className'        => 'bailaya-loc-grid',
            'itemClassName'    => 'bailaya-loc-card',
            'nameClassName'    => 'bailaya-loc-name',
            'addressClassName' => 'bailaya-loc-address',
            'badgeClassName'   => 'bailaya-loc-badge',
            'linkClassName'    => 'bailaya-loc-link',
            'labels'           => [
                'primary'    => __('Primary', 'bailaya'),
                'directions' => __('Directions', 'bailaya'),
            ],
        ];
        $data = array_replace_recursive($defaults, $opts);

        ob_start();
        $template = BAILAYA_WP_PATH . 'templates/location-list.php';
        /** @var list<StudioLocation> $locations */
        include $template;
        return (string) ob_get_clean();
    }

    /**
     * Render a studio profile card via template.
     *
     * @param StudioProfile $profile
     * @param array{
     *   locale?: string,
     *   showAllLocations?: bool,
     *   className?: string,
     *   itemClassName?: string,
     *   imageWrapperClassName?: string,
     *   imageClassName?: string,
     *   bodyClassName?: string,
     *   nameClassName?: string,
     *   descriptionClassName?: string,
     *   labelClassName?: string,
     *   locationClassName?: string,
     *   locationNameClassName?: string,
     *   labels?: array{
     *     addressLabel?: string,
     *     businessHoursLabel?: string
     *   }
     * } $opts
     */
    public static function studioProfileCard(StudioProfile $profile, array $opts = []): string
    {
        wp_enqueue_style('bailaya');

        $defaults = [
            'locale' => 'en',
            'showAllLocations' => false,
            'className' => 'bailaya-studio mt-6 md:mt-12 space-y-8',
            'itemClassName' => 'bailaya-studio-card flex flex-col md:flex-row items-center rounded-lg border border-[#DCDCDC] shadow-lg overflow-hidden',
            'imageWrapperClassName' => 'bailaya-studio-imgwrap w-full p-4 pb-0 md:pb-4 md:w-1/3 aspect-square',
            'imageClassName' => 'bailaya-studio-img w-full h-full rounded-xl object-cover',
            'bodyClassName' => 'bailaya-studio-body p-6 flex-1 text-left',
            'nameClassName' => 'bailaya-studio-name text-xl md:text-3xl font-semibold text-[#2A2343]',
            'descriptionClassName' => 'bailaya-studio-desc mt-2 text-sm md:text-lg text-[#464646]',
            'labelClassName' => 'bailaya-studio-label mt-1 text-sm text-gray-600',
            'locationClassName' => 'bailaya-studio-location mt-1 text-sm text-gray-600',
            'locationNameClassName' => 'bailaya-studio-location-name font-semibold',
            'labels' => [
                'addressLabel' => '',
                'businessHoursLabel' => '',
            ],
        ];

        $data = array_replace_recursive($defaults, $opts);
        $data['profile'] = $profile;

        ob_start();
        $template = BAILAYA_WP_PATH . 'templates/studio-profile-card.php';
        include $template;
        return (string)ob_get_clean();
    }

    /**
     * Render a user profile card.
     *
     * @param UserProfile $profile
     * @param array{
     *   locale?: string,
     *   className?: string,
     *   itemClassName?: string,
     *   imageWrapperClassName?: string,
     *   imageClassName?: string,
     *   bodyClassName?: string,
     *   nameClassName?: string,
     *   bioClassName?: string,
     *   labelClassName?: string,
     *   labels?: array{ occupationLabel?: string, experienceLabel?: string }
     * } $opts
     */
    public static function userProfileCard(UserProfile $profile, array $opts = []): string
    {
        wp_enqueue_style('bailaya');

        $defaults = [
            'locale' => 'en',
            'className' => 'bailaya-user mt-6 md:mt-12 space-y-8',
            'itemClassName' => 'bailaya-user-card flex flex-col md:flex-row items-center rounded-lg border border-[#DCDCDC] shadow-lg overflow-hidden',
            'imageWrapperClassName' => 'bailaya-user-imgwrap w-full p-4 pb-0 md:pb-4 md:w-1/3 aspect-square',
            'imageClassName' => 'bailaya-user-img w-full h-full rounded-xl object-cover',
            'bodyClassName' => 'bailaya-user-body p-6 flex-1 text-left',
            'nameClassName' => 'bailaya-user-name text-xl md:text-3xl font-semibold text-[#2A2343]',
            'bioClassName' => 'bailaya-user-bio mt-2 text-sm md:text-lg text-[#464646]',
            'labelClassName' => 'bailaya-user-label mt-1 text-sm text-gray-600',
            'labels' => [
                'occupationLabel' => '',
                'experienceLabel' => '',
            ],
        ];
        $data = array_replace_recursive($defaults, $opts);
        $data['profile'] = $profile;

        ob_start();
        $template = BAILAYA_WP_PATH . 'templates/user-profile-card.php';
        include $template;
        return (string)ob_get_clean();
    }
}
