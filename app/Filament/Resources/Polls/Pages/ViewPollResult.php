<?php

namespace App\Filament\Resources\Polls\Pages;

use App\Filament\Resources\Polls\PollResource;
use App\Filament\Widgets\VoteActivityTable;
use App\Filament\Widgets\VoteStatsOverview;
use App\Models\Poll;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class ViewPollResult extends Page
{
    protected static string $resource = PollResource::class;

    protected string $view = 'filament.resources.polls.pages.view-poll-result';

    public Poll $record;

    public function mount($record): void
    {   
        
        $this->record = Poll::with('options')->where('id', $record->id)->first();
        
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_public')
                ->label('View public page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url($this->record->share_url)
                ->openUrlInNewTab(),

            Action::make('edit')
                ->label('Edit poll')
                ->icon('heroicon-o-pencil')
                ->url(PollResource::getUrl('edit', ['record' => $this->record])),
        ];
    }

    public function getTitle(): string
    {
        return  $this->record->question;
    }

    protected function getFooterWidgets(): array
    {
        return [
            VoteStatsOverview::make(['pollId' => $this->record->id]),
            VoteActivityTable::make(['pollId' => $this->record->id]),
        ];
    }

}