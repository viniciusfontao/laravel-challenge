<?php

namespace App\Business;

use App\Repositories\EventsRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EventsBusiness
{
    private $eventsRepository;

    public function __construct(EventsRepository $eventsRepository)
    {
        $this->eventsRepository = $eventsRepository;
    }

    public function getTodayEvents()
    {
        $startDate = Carbon::now()->setTimeFromTimeString('00:00:00')->toDateTimeString();
        $endDate = Carbon::now()->setTimeFromTimeString('23:59:59')->toDateTimeString();
        return $this->getEventsFromDate($startDate, $endDate);
    }

    public function getFiveDayEvents()
    {
        $startDate = Carbon::now()->setTimeFromTimeString('00:00:00')->toDateTimeString();
        $endDate = Carbon::now()->addDay(5)->setTimeFromTimeString('23:59:59')->toDateTimeString();
        return $this->getEventsFromDate($startDate, $endDate);
    }

    private function getEventsFromDate($startDate, $endDate)
    {
        return $this->eventsRepository->getEventsFromDate($startDate, $endDate);
    }

    public function getAllPaginated()
    {
        return $this->eventsRepository->paginate();
    }

    public function create(array $data)
    {
        $data = $this->formatDateToInsert($data);
        $data['user_id'] = Auth::user()->id;
        $participantsIds = $data['participants'] ?? [];
        unset($data['participants']);
        $event = $this->eventsRepository->create($data);
        if (!empty($participantsIds)) {
            $this->eventsRepository->syncParticipants($event, $participantsIds);
        }
        return true;
    }

    public function find($id)
    {
        return $this->eventsRepository->find($id);
    }

    public function delete($id)
    {
        try {
            $validate = $this->validate($id, 'delete');
            if (!$validate['success']) {
                return $validate;
            }

            $this->eventsRepository->delete($id);
            return [
                'success' => true,
                'message' => "Event {$id} deleted!",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Error on deleting Event with id: {$id}",
                'exception' => $e->getMessage(),
            ];
        }
    }

    public function validate($id, $actionMessage)
    {
        $event = $this->eventsRepository->find($id);
        if (!$event) {
            return [
                'success' => false,
                'message' => "Event with id: {$id} not found!",
            ];
        }

        if ($event->user_id !== Auth::user()->id) {
            return [
                'success' => false,
                'message' => "You cant {$actionMessage} this event because you are not the owner.",
            ];
        }

        return [
            'success' => true,
            'event' => $event,
        ];
    }

    public function update($id, $data)
    {
        $validate = $this->validate($id, 'update');
        if (!$validate['success']) {
            return $validate;
        }

        $participantsIds = $data['participants'] ?? [];
        unset($data['participants']);
        $data = $this->formatDateToInsert($data);
        $this->eventsRepository->update($validate['event'], $data);
        if (!empty($participantsIds)) {
            $this->eventsRepository->syncParticipants($validate['event'], $participantsIds);
        }
        return [
            'success' => true,
            'message' => "Event with id: {$id} updated!",
        ];
    }

    private function formatDateToInsert($data)
    {
        $data['start_date'] = Carbon::parse($data['start_date'])->toDateTimeString();
        $data['end_date'] = Carbon::parse($data['end_date'])->toDateTimeString();
        return $data;
    }

    public function createEventsFromCSV(array $events)
    {
        foreach ($events as $event) {
            $this->create($event);
        }
        return true;
    }
}
