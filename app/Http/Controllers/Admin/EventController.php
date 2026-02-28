<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');
        $from   = $request->query('from', '');
        $to     = $request->query('to', '');

        $query = Event::with('media')->orderByDesc('event_date');

        if ($q !== '') {
            $query->where(function ($qb) use ($q): void {
                $qb->where('title', 'like', '%' . $q . '%')
                   ->orWhere('slug', 'like', '%' . $q . '%');
            });
        }

        if ($status === 'published' || $status === 'draft') {
            $query->where('status', $status);
        }

        if ($from !== '') {
            $query->whereDate('event_date', '>=', $from);
        }

        if ($to !== '') {
            $query->whereDate('event_date', '<=', $to);
        }

        $events = $query->paginate(20)->withQueryString();

        return view('admin.events.index', compact('events', 'q', 'status', 'from', 'to'));
    }

    public function create(): View
    {
        return view('admin.events.create');
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = Event::create($request->validated());

        return redirect()->route('admin.events.edit', $event)
            ->with('success', 'Evento creato con successo.');
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return redirect()->route('admin.events.edit', $event)
            ->with('success', 'Evento aggiornato con successo.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Evento eliminato.');
    }
}
