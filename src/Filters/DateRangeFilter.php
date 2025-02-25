<?php

namespace Malzariey\FilamentDaterangepickerFilter\Filters;

use Carbon\CarbonInterface;
use Closure;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class DateRangeFilter extends BaseFilter
{
    protected string $column;

    protected bool $displayRangeInLine = false;

    protected CarbonInterface|string|Closure|null $maxDate = null;

    protected CarbonInterface|string|Closure|null $minDate = null;

    protected CarbonInterface|string|Closure|null $startDate = null;

    protected CarbonInterface|string|Closure|null $endDate = null;

    protected string|Closure|null $displayFormat = "DD/MM/YYYY";

    protected string|Closure|null $format = 'd/m/Y';

    protected string|Closure|null $timezone = null;

    protected bool|Closure $alwaysShowCalender = true;

    protected int|null $firstDayOfWeek = 1;

    protected bool $timePicker = false;

    protected int $timePickerIncrement = 30;

    protected bool $autoApply = false;

    protected bool $linkedCalendars = true;

    protected array|Closure $disabledDates = [];

    protected int|Closure|null $hoursStep = null;

    protected int|Closure|null $minutesStep = null;

    protected int|Closure|null $secondsStep = null;

    public function resetFirstDayOfWeek(): static
    {
        $this->firstDayOfWeek($this->getDefaultFirstDayOfWeek());

        return $this;
    }

    public function firstDayOfWeek(int|null $day): static
    {
        if ($day < 0 || $day > 7) {
            $day = $this->getDefaultFirstDayOfWeek();
        }

        $this->firstDayOfWeek = $day;

        return $this;
    }

    public function withIndicater(): self
    {

        $this->indicateUsing(function (array $data): ?string {
            if (!$data[$this->column]) {
                return null;
            }
            return __('filament-daterangepicker-filter::message.period') . ' ' . ($this->label ? "[$this->label] " : "") . $data[$this->column];
        });

        return $this;
    }

    public function operator(string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }


    //Javascript Format

    public function defaultToday()
    {
        $this->startDate = Carbon::now();

        $this->endDate = Carbon::now();

        return $this;
    }

    //State Format

    public function getFormSchema(): array
    {
        $schema = $this->evaluate($this->formSchema);

        if ($schema !== null) {
            return $schema;
        }
        $this->setUp();
        $defult = null;

        if ($this->startDate != null && $this->endDate != null) {
            $defult = $this->startDate->format($this->format) . " - " . $this->endDate->format($this->format);
        } else if ($this->startDate != null && $this->endDate == null) {
            $defult = $this->startDate->format($this->format) . " - " . $this->startDate->format($this->format);
        } else if ($this->startDate == null && $this->endDate != null) {
            $defult = $this->endDate->format($this->format) . " - " . $this->endDate->format($this->format);
        }

        return [
            DateRangePicker::make($this->column)
                ->default($defult)
                ->label($this->getLabel())
                ->timezone($this->timezone)
                ->startDate($this->startDate)
                ->endDate($this->endDate)
                ->firstDayOfWeek($this->firstDayOfWeek)
                ->alwaysShowCalender($this->alwaysShowCalender)
                ->setTimePickerOption($this->timePicker)
                ->setTimePickerIncrementOption($this->timePickerIncrement)
                ->setAutoApplyOption($this->autoApply)
                ->setLinkedCalendarsOption($this->linkedCalendars)
                ->disabledDates($this->disabledDates)
                ->minDate($this->minDate)
                ->maxDate($this->maxDate)
                ->displayFormat($this->displayFormat)
                ->format($this->format)
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->useColumn($this->getName());
        $this->query(fn($query, $data) => $this->dateRangeQuery($query, $data));
//        $this->make()
//        $this->default($this->startDate->format($this->displayFormat) + " - " + $this->endDate->format($this->displayFormat));
    }

    public function useColumn(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    public function dateRangeQuery(Builder $query, array $data = []): Builder
    {
        if (is_null($data[$this->column])) {
            return $query;
        }

        $dates = explode(' ', $data[$this->column]);

        if (count($dates) == 3) {
            $from = $dates[0];
            $to = $dates[2];
        } else {
            $from = null;
            $to = null;
        }

        return $query
            ->when(
                $from !== null && $to !== null,
                fn(Builder $query, $date): Builder => $query->whereBetween($this->column, [
                    Carbon::createFromFormat($this->format, $from)->startOfDay(),
                    Carbon::createFromFormat($this->format, $to)->endOfDay(),
                ]),
            );
    }

    public function format(string|Closure|null $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function displayFormat(string|Closure|null $format): static
    {
        $this->displayFormat = $format;

        return $this;
    }

    public function maxDate(CarbonInterface|string|Closure|null $date): static
    {
        $this->maxDate = $date;

        return $this;
    }

    public function minDate(CarbonInterface|string|Closure|null $date): static
    {
        $this->minDate = $date;

        return $this;
    }

    public function disabledDates(array|Closure $dates): static
    {
        $this->disabledDates = $dates;

        return $this;
    }

    public function setLinkedCalendarsOption(bool $condition = true): static
    {
        $this->linkedCalendars = $condition;

        return $this;
    }

    public function setAutoApplyOption(bool $condition = true): static
    {
        $this->autoApply = $condition;

        return $this;
    }

    public function setTimePickerIncrementOption(int $increment = 1): static
    {
        $this->timePickerIncrement = $increment;

        return $this;
    }

    public function setTimePickerOption(bool $condition = true): static
    {
        $this->timePicker = $condition;

        return $this;
    }

    public function endDate(CarbonInterface|string|Closure|null $date): static
    {
        $this->endDate = $date;

        return $this;
    }

    public function startDate(CarbonInterface|string|Closure|null $date): static
    {
        $this->startDate = $date;

        return $this;
    }

    public function timezone(string|Closure|null $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }


}
