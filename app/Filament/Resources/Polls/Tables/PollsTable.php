<?php

namespace App\Filament\Resources\Polls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\Poll;
use App\Filament\Resources\Polls\PollResource;

class PollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),

                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Closed')
                    ->colors([
                        'success' => true,
                        'danger'  => false,
                    ]),

                TextColumn::make('votes_count')
                    ->label('Votes')
                    ->counts('votes')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Closes')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No end date'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                
            ])
            ->recordActions([
                
                Action::make('view_public')
                ->label('View public page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (Poll $record) => $record->share_url)
                ->openUrlInNewTab(),
                Action::make('results')
                    ->label('View Results')
                    ->icon('heroicon-o-chart-pie')
                    ->url(fn (Poll $record) => PollResource::getUrl('results', ['record' => $record])),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
