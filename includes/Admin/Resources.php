<?php
declare(strict_types=1);

namespace BailaYaWP\Admin;

use BailaYa\Client;

if (!defined('ABSPATH')) exit;

/**
 * Declarative descriptions of the Management API resources exposed in wp-admin.
 *
 * Each descriptor drives the generic list/create/edit/delete screens in
 * {@see ManagementPage}, so adding a resource means adding a definition here
 * rather than a new screen.
 *
 * Field keys:
 *   name        Property name sent to the API and read back off the DTO.
 *   label       Form label.
 *   type        text | textarea | number | date | time | email | checkbox | select
 *   required    Enforced by the browser and re-checked on save.
 *   create_only The API rejects it on update (e.g. a class's date/recurrence).
 *   edit_only   Only meaningful when updating (e.g. a package's isActive).
 *   cast        int | float — how to coerce the submitted string.
 *   options     Fixed select options (value => label).
 *   options_from  Populate the select from the API: team | rooms | locations.
 *   help        Description shown under the field.
 */
final class Resources
{
    /** @return array<string, array<string,mixed>> */
    public static function all(): array
    {
        return [
            'classes' => [
                'label' => __('Classes', 'bailaya'),
                'singular' => __('Class', 'bailaya'),
                'columns' => [
                    'name' => __('Name', 'bailaya'),
                    'discipline' => __('Discipline', 'bailaya'),
                    'level' => __('Level', 'bailaya'),
                    'date' => __('Date', 'bailaya'),
                    'startTime' => __('Start', 'bailaya'),
                    'endTime' => __('End', 'bailaya'),
                    'room' => __('Room', 'bailaya'),
                ],
                'list'   => fn(Client $c, array $p) => $c->listClasses($p),
                'create' => fn(Client $c, array $i) => $c->createClass($i),
                'read'   => fn(Client $c, string $id) => $c->getClass($id),
                'update' => fn(Client $c, string $id, array $i) => $c->updateClass($id, $i),
                'delete' => fn(Client $c, string $id) => $c->deleteClass($id),
                'fields' => self::classFields(withStudioType: true),
            ],

            'events' => [
                'label' => __('Events', 'bailaya'),
                'singular' => __('Event', 'bailaya'),
                'columns' => [
                    'name' => __('Name', 'bailaya'),
                    'discipline' => __('Discipline', 'bailaya'),
                    'level' => __('Level', 'bailaya'),
                    'date' => __('Date', 'bailaya'),
                    'startTime' => __('Start', 'bailaya'),
                    'endTime' => __('End', 'bailaya'),
                    'room' => __('Room', 'bailaya'),
                ],
                'list'   => fn(Client $c, array $p) => $c->listEvents($p),
                'create' => fn(Client $c, array $i) => $c->createEvent($i),
                'read'   => fn(Client $c, string $id) => $c->getEvent($id),
                'update' => fn(Client $c, string $id, array $i) => $c->updateEvent($id, $i),
                'delete' => fn(Client $c, string $id) => $c->deleteEvent($id),
                // An event is a class with no studio type, so the field is omitted.
                'fields' => self::classFields(withStudioType: false),
            ],

            'students' => [
                'label' => __('Students', 'bailaya'),
                'singular' => __('Student', 'bailaya'),
                'columns' => [
                    'name' => __('First name', 'bailaya'),
                    'lastname' => __('Last name', 'bailaya'),
                    'email' => __('Email', 'bailaya'),
                    'level' => __('Level', 'bailaya'),
                    'phone' => __('Phone', 'bailaya'),
                    'status' => __('Status', 'bailaya'),
                ],
                'list'   => fn(Client $c, array $p) => $c->listStudents($p),
                'create' => fn(Client $c, array $i) => $c->createStudent($i),
                'read'   => fn(Client $c, string $id) => $c->getStudent($id),
                'update' => fn(Client $c, string $id, array $i) => $c->updateStudent($id, $i),
                'delete' => fn(Client $c, string $id) => $c->deleteStudent($id),
                'fields' => [
                    ['name' => 'name',     'label' => __('First name', 'bailaya'), 'type' => 'text',  'required' => true],
                    ['name' => 'lastname', 'label' => __('Last name', 'bailaya'),  'type' => 'text',  'required' => true],
                    ['name' => 'email',    'label' => __('Email', 'bailaya'),      'type' => 'email', 'required' => true],
                    ['name' => 'level',    'label' => __('Level', 'bailaya'),      'type' => 'text',  'required' => true, 'help' => __('e.g. Beginner, Intermediate, Advanced.', 'bailaya')],
                    ['name' => 'phone',    'label' => __('Phone', 'bailaya'),      'type' => 'text'],
                    ['name' => 'status',   'label' => __('Status', 'bailaya'),     'type' => 'text'],
                ],
            ],

            'instructors' => [
                'label' => __('Instructors', 'bailaya'),
                'singular' => __('Instructor', 'bailaya'),
                'columns' => [
                    'name' => __('First name', 'bailaya'),
                    'lastname' => __('Last name', 'bailaya'),
                    'email' => __('Email', 'bailaya'),
                    'role' => __('Role', 'bailaya'),
                    'status' => __('Status', 'bailaya'),
                ],
                'list'   => fn(Client $c, array $p) => $c->listInstructors($p),
                'create' => fn(Client $c, array $i) => $c->createInstructor($i),
                'read'   => fn(Client $c, string $id) => $c->getInstructor($id),
                // The API exposes no update for instructors — edit via Team.
                'delete' => fn(Client $c, string $id) => $c->deleteInstructor($id),
                'fields' => [
                    ['name' => 'name',     'label' => __('First name', 'bailaya'), 'type' => 'text',  'required' => true],
                    ['name' => 'lastname', 'label' => __('Last name', 'bailaya'),  'type' => 'text'],
                    ['name' => 'email',    'label' => __('Email', 'bailaya'),      'type' => 'email', 'required' => true],
                    ['name' => 'phone',    'label' => __('Phone', 'bailaya'),      'type' => 'text'],
                ],
            ],

            'team' => [
                'label' => __('Team', 'bailaya'),
                'singular' => __('Team member', 'bailaya'),
                'columns' => [
                    'name' => __('First name', 'bailaya'),
                    'lastname' => __('Last name', 'bailaya'),
                    'email' => __('Email', 'bailaya'),
                    'role' => __('Role', 'bailaya'),
                    'status' => __('Status', 'bailaya'),
                ],
                'list'   => fn(Client $c, array $p) => $c->listTeam($p),
                'create' => fn(Client $c, array $i) => $c->createTeamMember($i),
                'read'   => fn(Client $c, string $id) => $c->getTeamMember($id),
                'delete' => fn(Client $c, string $id) => $c->deleteTeamMember($id),
                'fields' => [
                    ['name' => 'name',     'label' => __('First name', 'bailaya'), 'type' => 'text',  'required' => true],
                    ['name' => 'lastname', 'label' => __('Last name', 'bailaya'),  'type' => 'text'],
                    ['name' => 'email',    'label' => __('Email', 'bailaya'),      'type' => 'email', 'required' => true],
                    ['name' => 'phone',    'label' => __('Phone', 'bailaya'),      'type' => 'text'],
                    ['name' => 'role',     'label' => __('Role', 'bailaya'),       'type' => 'select', 'required' => true, 'options' => [
                        'instructor' => __('Instructor', 'bailaya'),
                        'staff' => __('Staff', 'bailaya'),
                        'admin' => __('Admin', 'bailaya'),
                        'owner' => __('Owner', 'bailaya'),
                    ]],
                ],
            ],

            'packages' => [
                'label' => __('Packages', 'bailaya'),
                'singular' => __('Package', 'bailaya'),
                'columns' => [
                    'name' => __('Name', 'bailaya'),
                    'price' => __('Price', 'bailaya'),
                    'sessions' => __('Sessions', 'bailaya'),
                    'durationMonths' => __('Valid (months)', 'bailaya'),
                    'isActive' => __('Active', 'bailaya'),
                ],
                'list'   => fn(Client $c, array $p) => $c->listPackages($p),
                'create' => fn(Client $c, array $i) => $c->createPackage($i),
                'read'   => fn(Client $c, string $id) => $c->getPackage($id),
                'update' => fn(Client $c, string $id, array $i) => $c->updatePackage($id, $i),
                'delete' => fn(Client $c, string $id) => $c->deletePackage($id),
                'fields' => [
                    ['name' => 'name',           'label' => __('Name', 'bailaya'),            'type' => 'text',   'required' => true],
                    ['name' => 'price',          'label' => __('Price', 'bailaya'),           'type' => 'number', 'required' => true, 'cast' => 'float', 'step' => '0.01'],
                    ['name' => 'sessions',       'label' => __('Sessions', 'bailaya'),        'type' => 'number', 'required' => true, 'cast' => 'int'],
                    ['name' => 'durationMonths', 'label' => __('Valid (months)', 'bailaya'),  'type' => 'number', 'required' => true, 'cast' => 'int'],
                    ['name' => 'description',    'label' => __('Description', 'bailaya'),     'type' => 'textarea'],
                    ['name' => 'isPrivateLesson', 'label' => __('Private lesson package', 'bailaya'), 'type' => 'checkbox'],
                    ['name' => 'privateDurationMins', 'label' => __('Private lesson duration (mins)', 'bailaya'), 'type' => 'number', 'cast' => 'int'],
                    ['name' => 'isActive',       'label' => __('Active', 'bailaya'),          'type' => 'checkbox', 'edit_only' => true],
                ],
            ],

            'rooms' => [
                'label' => __('Rooms', 'bailaya'),
                'singular' => __('Room', 'bailaya'),
                'columns' => [
                    'name' => __('Name', 'bailaya'),
                    'capacity' => __('Capacity', 'bailaya'),
                    'studioLocationId' => __('Location', 'bailaya'),
                ],
                'list'   => fn(Client $c, array $p) => $c->listRooms($p),
                'create' => fn(Client $c, array $i) => $c->createRoom($i),
                'read'   => fn(Client $c, string $id) => $c->getRoom($id),
                'update' => fn(Client $c, string $id, array $i) => $c->updateRoom($id, $i),
                'delete' => fn(Client $c, string $id) => $c->deleteRoom($id),
                'fields' => [
                    ['name' => 'name',     'label' => __('Name', 'bailaya'),     'type' => 'text',   'required' => true, 'help' => __('Unique per studio.', 'bailaya')],
                    ['name' => 'capacity', 'label' => __('Capacity', 'bailaya'), 'type' => 'number', 'cast' => 'int'],
                    ['name' => 'studioLocationId', 'label' => __('Location', 'bailaya'), 'type' => 'select', 'options_from' => 'locations', 'help' => __('Defaults to the studio\'s primary location.', 'bailaya')],
                ],
            ],

            'locations' => [
                'label' => __('Locations', 'bailaya'),
                'singular' => __('Location', 'bailaya'),
                'columns' => [
                    'name' => __('Name', 'bailaya'),
                    'addressLine1' => __('Address', 'bailaya'),
                    'city' => __('City', 'bailaya'),
                    'country' => __('Country', 'bailaya'),
                    'isPrimary' => __('Primary', 'bailaya'),
                ],
                'list'   => fn(Client $c, array $p) => $c->listLocations(),
                'create' => fn(Client $c, array $i) => $c->createLocation($i),
                'read'   => fn(Client $c, string $id) => $c->getLocation($id),
                'update' => fn(Client $c, string $id, array $i) => $c->updateLocation($id, $i),
                'delete' => fn(Client $c, string $id) => $c->deleteLocation($id),
                'paginated' => false,
                'fields' => [
                    ['name' => 'name',         'label' => __('Name', 'bailaya'),            'type' => 'text', 'required' => true],
                    ['name' => 'addressLine1', 'label' => __('Address line 1', 'bailaya'),  'type' => 'text'],
                    ['name' => 'addressLine2', 'label' => __('Suite / Unit / Floor', 'bailaya'), 'type' => 'text'],
                    ['name' => 'city',         'label' => __('City', 'bailaya'),            'type' => 'text'],
                    ['name' => 'state',        'label' => __('State / Region', 'bailaya'),  'type' => 'text'],
                    ['name' => 'postalCode',   'label' => __('Postal code', 'bailaya'),     'type' => 'text'],
                    ['name' => 'country',      'label' => __('Country', 'bailaya'),         'type' => 'text', 'help' => __('Two-letter ISO code, e.g. MX.', 'bailaya')],
                    ['name' => 'latitude',     'label' => __('Latitude', 'bailaya'),        'type' => 'number', 'cast' => 'float', 'step' => 'any'],
                    ['name' => 'longitude',    'label' => __('Longitude', 'bailaya'),       'type' => 'number', 'cast' => 'float', 'step' => 'any'],
                    ['name' => 'isPrimary',    'label' => __('Primary location', 'bailaya'), 'type' => 'checkbox', 'help' => __('Promoting a location demotes the current primary. A studio always keeps exactly one.', 'bailaya')],
                ],
            ],
        ];
    }

    /** @return array<string,mixed>|null */
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /**
     * Fields shared by classes and events. Events are classes with no studio
     * type, so only that one field differs.
     *
     * @return list<array<string,mixed>>
     */
    private static function classFields(bool $withStudioType): array
    {
        $fields = [
            ['name' => 'name',         'label' => __('Name', 'bailaya'),       'type' => 'text', 'required' => true],
            ['name' => 'discipline',   'label' => __('Discipline', 'bailaya'), 'type' => 'text', 'required' => true, 'help' => __('e.g. Salsa.', 'bailaya')],
            ['name' => 'level',        'label' => __('Level', 'bailaya'),      'type' => 'text', 'required' => true, 'help' => __('e.g. Beginner.', 'bailaya')],
            ['name' => 'date',         'label' => __('Date', 'bailaya'),       'type' => 'date', 'required' => true, 'create_only' => true],
            ['name' => 'startTime',    'label' => __('Start time', 'bailaya'),  'type' => 'time', 'required' => true],
            ['name' => 'endTime',      'label' => __('End time', 'bailaya'),    'type' => 'time', 'required' => true],
            ['name' => 'teamMemberId', 'label' => __('Instructor', 'bailaya'),  'type' => 'select', 'required' => true, 'options_from' => 'team'],
            ['name' => 'repeatUntil',  'label' => __('Repeat weekly until', 'bailaya'), 'type' => 'date', 'create_only' => true, 'help' => __('Leave blank for a one-off.', 'bailaya')],
        ];

        if ($withStudioType) {
            $fields[] = [
                'name' => 'studioTypeId',
                'label' => __('Dance type', 'bailaya'),
                'type' => 'select',
                'options_from' => 'studio-types',
                'create_only' => true,
                'help' => __('Leave blank to create an event instead of a class.', 'bailaya'),
            ];
        }

        return array_merge($fields, [
            ['name' => 'roomId',   'label' => __('Room', 'bailaya'),     'type' => 'select', 'options_from' => 'rooms'],
            ['name' => 'location', 'label' => __('One-off location override', 'bailaya'), 'type' => 'text'],
            ['name' => 'capacity', 'label' => __('Capacity', 'bailaya'), 'type' => 'number', 'cast' => 'int'],
            ['name' => 'price',    'label' => __('Price', 'bailaya'),    'type' => 'number', 'cast' => 'float', 'step' => '0.01'],
            ['name' => 'allowPackages',   'label' => __('Allow packages', 'bailaya'),   'type' => 'checkbox'],
            ['name' => 'requirePackage',  'label' => __('Require a package', 'bailaya'), 'type' => 'checkbox'],
            ['name' => 'isVirtual',       'label' => __('Held online', 'bailaya'),      'type' => 'checkbox'],
            ['name' => 'virtualPlatform', 'label' => __('Virtual platform', 'bailaya'), 'type' => 'select', 'options' => [
                'ZOOM' => __('Zoom', 'bailaya'),
                'GOOGLE_MEET' => __('Google Meet', 'bailaya'),
                'TEAMS' => __('Teams', 'bailaya'),
            ]],
        ]);
    }
}
