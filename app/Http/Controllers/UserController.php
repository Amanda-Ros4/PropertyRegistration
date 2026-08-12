<?php

namespace App\Http\Controllers;

use App\Enums\ActiveStatus;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserActiveRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use App\Support\Flash;
use App\Support\SearchInput;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = [
            'search' => SearchInput::sanitize($request->input('search')),
        ];

        $users = $this->userService->list($filters, perPage: 15);
        $actor = $request->user();

        return Inertia::render('Users/Index', [
            'users' => $users->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->profile->value,
                'active' => $user->active->value,
                'can_update' => $actor->can('update', $user),
            ]),
            'filters' => $filters,
            'canCreate' => $actor->can('create', User::class),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create', [
            'profileOptions' => $this->userService->profileOptionsFor($request->user()),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->create($request->user(), $request->validated());

        return redirect()->route('users.index')
            ->with('flash', Flash::success(__('users.created')));
    }

    public function edit(Request $request, User $user): Response
    {
        $this->authorize('view', $user);

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cpf' => $user->cpf,
                'profile' => $user->profile->value,
                'active' => $user->active->value,
            ],
            'profileOptions' => $this->userService->profileOptionsFor($request->user()),
            'canUpdate' => $request->user()->can('update', $user),
            'canChangeProfile' => $this->userService->canChangeProfile($request->user(), $user),
            'isSelf' => (int) $request->user()->id === (int) $user->id,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userService->update($request->user(), $user, $request->validated());

        return redirect()->route('users.index')
            ->with('flash', Flash::success(__('users.updated')));
    }

    public function updateActive(UpdateUserActiveRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $active = ActiveStatus::from($request->validated('active'));
        $this->userService->updateActive($request->user(), $user, $active);

        $message = $active === ActiveStatus::Active
            ? __('users.status_activated')
            : __('users.status_deactivated');

        return redirect()->back()
            ->with('flash', Flash::success($message));
    }
}
