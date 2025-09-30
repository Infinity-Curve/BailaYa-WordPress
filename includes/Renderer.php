<?php
declare(strict_types=1);

namespace BailaYaWP;

use BailaYa\Dto\StudioClass;

if (!defined('ABSPATH')) exit;

final class Renderer
{
    /**
     * Render a schedule list using a PHP template.
     *
     * @param list<StudioClass> $classes
     * @param array{
     *   locale?: string,
     *   labels?: array{ instructor?: string },
     *   className?: string,
     *   itemClassName?: string,
     *   nameClassName?: string,
     *   detailsClassName?: string,
     *   instructorClassName?: string
     * } $opts
     */
    public static function classSchedule(array $classes, array $opts = []): string
    {
        wp_enqueue_style('bailaya');

        $defaults = [
            'locale' => get_locale() ?: 'en',
            'labels' => ['instructor' => __('Instructor:', 'bailaya')],
            'className' => 'bailaya-list',
            'itemClassName' => 'bailaya-card',
            'nameClassName' => 'bailaya-name',
            'detailsClassName' => 'bailaya-details',
            'instructorClassName' => 'bailaya-instructor',
        ];
        $data = array_replace_recursive($defaults, $opts);

        ob_start();
        $template = BAILAYA_WP_PATH . 'templates/class-schedule.php';
        /** @var list<StudioClass> $classes */
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
     * Render a studio profile card via template.
     *
     * @param StudioProfile $profile
     * @param array{
     *   locale?: string,
     *   className?: string,
     *   itemClassName?: string,
     *   imageWrapperClassName?: string,
     *   imageClassName?: string,
     *   bodyClassName?: string,
     *   nameClassName?: string,
     *   descriptionClassName?: string,
     *   labelClassName?: string,
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
            'className' => 'bailaya-studio mt-6 md:mt-12 space-y-8',
            'itemClassName' => 'bailaya-studio-card flex flex-col md:flex-row items-center rounded-lg border border-[#DCDCDC] shadow-lg overflow-hidden',
            'imageWrapperClassName' => 'bailaya-studio-imgwrap w-full p-4 pb-0 md:pb-4 md:w-1/3 aspect-square',
            'imageClassName' => 'bailaya-studio-img w-full h-full rounded-xl object-cover',
            'bodyClassName' => 'bailaya-studio-body p-6 flex-1 text-left',
            'nameClassName' => 'bailaya-studio-name text-xl md:text-3xl font-semibold text-[#2A2343]',
            'descriptionClassName' => 'bailaya-studio-desc mt-2 text-sm md:text-lg text-[#464646]',
            'labelClassName' => 'bailaya-studio-label mt-1 text-sm text-gray-600',
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
