<?php

namespace Corcel\Acf\Field;

use Carbon\Carbon;

/**
 * Class DateTime.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class DateTime extends BasicField
{
    /**
     * @var Carbon
     */
    protected $date;

    /**
     * @param string $fieldName
     */
    public function process()
    {
        $dateString = $this->fetchValue();
        $format = $this->getDateFormatFromString($dateString);
        $this->date = Carbon::createFromFormat($format, $dateString);
    }

    /**
     * @return Carbon
     */
    public function get()
    {
        return $this->date;
    }

    /**
     * @param string $dateString
     *
     * @return string
     */
    protected function getDateFormatFromString($dateString)
    {
        if (preg_match('/^\d{8}$/', $dateString)) { // 20161013 => date only
            return 'Ymd';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dateString)) { // 2016-10-19 08:06:05
            return 'Y-m-d H:i:s';
        } elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $dateString)) { // 17:30:00
            return 'H:i:s';
        } elseif (preg_match('/^\d{2}:\d{2}$/', $dateString)) { // 17:30
            return 'H:i';
        }
    }

    public function update($value)
    {
        if ($this->type == 'date_picker' && \Illuminate\Support\Carbon::canBeCreatedFromFormat($value, 'Y-m-d')) {
            $value = Carbon::createFromFormat('Y-m-d', $value)->format('Ymd');
        }
        if ($this->type == 'date_time_picker') {
            $value = \Illuminate\Support\Carbon::parse($value)->toDateTimeString();
        }
        parent::update($value);
    }
}
