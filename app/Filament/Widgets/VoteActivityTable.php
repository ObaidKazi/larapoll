<?php

namespace App\Filament\Widgets;

use App\Models\Vote;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class VoteActivityTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '5s';

    public ?int $pollId = null;

    public function table(Table $table): Table
    {

        return $table
            ->query($this->getQuery())
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('poll.question')
                    ->label('Poll')
                    ->limit(40)
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn($record) => $record->poll?->question),

                TextColumn::make('option.label')
                    ->label('Option chosen')
                    ->badge()
                    ->color('primary')
                    ->searchable(),


                TextColumn::make('ip_address')
                    ->label('IP address')
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('IP copied')
                    ->default('—'),



                TextColumn::make('created_at')
                    ->label('Time')
                    ->since()
                    ->sortable()
                    ->tooltip(fn($record) => $record->created_at->format('d M Y, H:i:s')),
            ])
            ->filters([
                SelectFilter::make('poll_id')
                    ->label('Poll')
                    ->relationship('poll', 'question')
                    ->searchable()
                    ->preload()
                    ->visible(fn() => !$this->pollId),

                Filter::make('today')
                    ->label('Today only')
                    ->query(fn(Builder $q) => $q->whereDate('created_at', today()))
                    ->toggle(),
            ])
            ->actions([

            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete selected votes'),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('5s'); 
    }

    protected function getQuery(): Builder
    {
        $query = Vote::query()
            ->with(['poll', 'option']);

        if ($this->pollId) {
            $query->where('poll_id', $this->pollId);
        }

        return $query;
    }



}
