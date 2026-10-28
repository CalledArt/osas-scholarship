<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class PageSkeleton extends Component
{
    /**
     * The pages the signed-in user can open from a link, each with the draft
     * drawn while it loads. The first matching path wins, so a fixed path
     * sits ahead of the wildcard that would also match it; `*` stands for one
     * path segment. A heading or strip label is given only where the page's
     * own is fixed; a record's heading comes from the link that opens it,
     * and so does the Act's, which only a student it covers should see.
     * `hide` names the parts of the shape that page does not have.
     *
     * @var list<array{path: string, shape: string, heading?: string, label?: string, hide?: list<string>}>
     */
    public array $pages;

    /**
     * The shapes those pages use, so a page carries only its role's drafts.
     *
     * @var list<string>
     */
    public array $shapes;

    public function __construct()
    {
        $user = Auth::user();

        $this->pages = $user->isAdmin() ? $this->adminPages($user) : $this->studentPages($user);
        $this->shapes = array_values(array_unique(array_column($this->pages, 'shape')));
    }

    /**
     * @return list<array{path: string, shape: string, heading?: string, label?: string, hide?: list<string>}>
     */
    private function studentPages(User $student): array
    {
        return [
            ['path' => route('dashboard', [], false), 'shape' => 'panes', 'heading' => $this->welcome($student), 'label' => __('Dashboard')],
            ['path' => route('scholarships.republic-act-10931', [], false), 'shape' => 'record', 'hide' => ['action']],
            ['path' => route('scholarships.index', [], false), 'shape' => 'panes', 'heading' => __('Scholarships'), 'hide' => ['id-box']],
            ['path' => route('scholarships.show', '*', false), 'shape' => 'leaf', 'hide' => ['fields']],
            ['path' => route('scholarships.apply', '*', false), 'shape' => 'leaf', 'hide' => ['mark', 'prose']],
            ['path' => route('scholarship-applications.index', [], false), 'shape' => 'cards', 'heading' => __('My Applications'), 'hide' => ['action']],
            ['path' => route('scholarship-applications.show', '*', false), 'shape' => 'record'],
            ['path' => route('profile.edit', [], false), 'shape' => 'profile', 'heading' => __('Profile')],
        ];
    }

    /**
     * @return list<array{path: string, shape: string, heading?: string, label?: string, hide?: list<string>}>
     */
    private function adminPages(User $admin): array
    {
        $form = ['mark', 'title-block', 'prose'];

        return [
            ['path' => route('admin.dashboard', [], false), 'shape' => 'overview', 'heading' => $this->welcome($admin), 'label' => __('Dashboard')],
            ['path' => route('admin.scholarships.index', [], false), 'shape' => 'cards', 'heading' => __('Manage Scholarships')],
            ['path' => route('admin.scholarships.create', [], false), 'shape' => 'leaf', 'heading' => __('Post Scholarship'), 'hide' => $form],
            ['path' => route('admin.scholarships.edit', '*', false), 'shape' => 'leaf', 'heading' => __('Edit Scholarship'), 'hide' => $form],
            ['path' => route('admin.scholarships.show', '*', false), 'shape' => 'record', 'hide' => ['title-block']],
            ['path' => route('admin.fund-allocation.index', [], false), 'shape' => 'cards', 'heading' => __('Fund Allocation'), 'hide' => ['action']],
            ['path' => route('admin.fund-allocation.show', '*', false), 'shape' => 'table', 'hide' => ['filters']],
            ['path' => route('admin.roster.index', [], false), 'shape' => 'table', 'heading' => __('Search Student'), 'hide' => ['action']],
            ['path' => route('admin.students.index', [], false), 'shape' => 'table', 'heading' => __('Edit Student Data'), 'hide' => ['action', 'filters']],
            ['path' => route('admin.students.edit', '*', false), 'shape' => 'form'],
            ['path' => route('profile.edit', [], false), 'shape' => 'profile', 'heading' => __('Profile')],
        ];
    }

    /**
     * The dashboards' greeting, word for word, so the draft's heading is the
     * page's own.
     */
    private function welcome(User $user): string
    {
        return __('Welcome back, :name!', ['name' => mb_strtoupper($user->surname_first_name)]);
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('components.page-skeleton');
    }
}
