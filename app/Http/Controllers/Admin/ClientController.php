<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveClientRequest;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Http\Requests\Admin\UpdateClientSectionRequest;
use App\Models\Client;
use App\Models\Section;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

/**
 * Supported-company logos (HOME-8, CLT-1, ADM-4).
 */
class ClientController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Client::class);

        return view('admin.clients.index', [
            'clients' => Client::query()->ordered()->get(),
            // Not saved until the visibility switch is submitted.
            'section' => Section::query()->firstOrNew(['key' => Section::HOME_CLIENTS], ['is_active' => true]),
        ]);
    } // End method

    public function updateSettings(UpdateClientSectionRequest $request): RedirectResponse
    {
        Section::query()->updateOrCreate(
            ['key' => Section::HOME_CLIENTS],
            ['is_active' => $request->validated('is_active')],
        );

        return redirect()->route('admin.clients.index')
            ->with('status', 'The supported companies section has been updated.');
    } // End method

    public function create(): View
    {
        Gate::authorize('create', Client::class);

        return view('admin.clients.form', ['client' => new Client(['is_active' => true])]);
    } // End method

    public function store(StoreClientRequest $request, ImageUploadService $images): RedirectResponse
    {
        $client = new Client($request->safe()->except('logo'));
        $client->logo = $this->storeLogo($request->file('logo'), $images);
        $client->sort_order = (int) Client::query()->max('sort_order') + 1;

        try {
            $client->save();
        } catch (Throwable $e) {
            $images->delete($client->logo);

            throw $e;
        }

        return redirect()->route('admin.clients.index')
            ->with('status', 'The logo has been added.');
    } // End method

    public function edit(Client $client): View
    {
        Gate::authorize('update', $client);

        return view('admin.clients.form', ['client' => $client]);
    } // End method

    public function update(UpdateClientRequest $request, Client $client, ImageUploadService $images): RedirectResponse
    {
        $client->fill($request->safe()->except('logo'));

        $oldLogo = $client->hasThemeLogo() ? null : $client->logo;
        $newLogo = null;

        if ($request->hasFile('logo')) {
            $newLogo = $this->storeLogo($request->file('logo'), $images);
            $client->logo = $newLogo;
        }

        // Only remove the old logo once the new path is saved, and never leave an orphaned upload.
        try {
            $client->save();
        } catch (Throwable $e) {
            $images->delete($newLogo);

            throw $e;
        }

        // Theme demo logos are read-only files and are never deleted.
        if ($newLogo) {
            $images->delete($oldLogo);
        }

        return redirect()->route('admin.clients.index')
            ->with('status', 'The logo has been updated.');
    } // End method

    public function destroy(Client $client, ImageUploadService $images): RedirectResponse
    {
        Gate::authorize('delete', $client);

        $logo = $client->hasThemeLogo() ? null : $client->logo;

        $client->delete();
        $images->delete($logo);

        return redirect()->route('admin.clients.index')
            ->with('status', 'The logo has been deleted.');
    } // End method

    public function toggle(Client $client): RedirectResponse
    {
        Gate::authorize('update', $client);

        $client->is_active = ! $client->is_active;
        $client->save();

        return redirect()->route('admin.clients.index')
            ->with('status', $client->is_active ? 'The logo is now shown on the site.' : 'The logo is now hidden from the site.');
    } // End method

    public function move(MoveClientRequest $request, Client $client): RedirectResponse
    {
        DB::transaction(function () use ($request, $client) {
            $clients = Client::query()->ordered()->lockForUpdate()->get()->values();
            $index = $clients->search(fn (Client $item) => $item->is($client));
            $target = $request->validated('direction') === 'left' ? $index - 1 : $index + 1;

            if ($index === false || ! $clients->has($target)) {
                return;
            }

            $order = $clients->all();
            [$order[$index], $order[$target]] = [$order[$target], $order[$index]];

            // Renumber the whole list so gaps or equal sort orders never cause surprises.
            foreach (array_values($order) as $position => $item) {
                $item->sort_order = $position + 1;
                $item->save();
            }
        });

        return redirect()->route('admin.clients.index')
            ->with('status', 'The order has been updated.');
    } // End method

    /**
     * Scale the uploaded logo down to fit the logo box and store it as WebP.
     */
    protected function storeLogo(UploadedFile $file, ImageUploadService $images): string
    {
        try {
            return $images->storeFitted($file, 'clients', Client::LOGO_MAX_WIDTH, Client::LOGO_MAX_HEIGHT);
        } catch (RuntimeException $e) {
            report($e);

            throw ValidationException::withMessages([
                'logo' => 'The logo could not be processed. Please try a different image.',
            ]);
        }
    } // End method
}
