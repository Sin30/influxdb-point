<?php

namespace Sin30\InfluxDB;

class Point
{
    /**
     * @var string
     */
    protected $measurement;

    /**
     * @var array
     */
    protected $fieldSet = [];

    /**
     * @var array
     */
    protected $tagSet = [];

    /**
     * @var \DateTime
     */
    protected $time;

    /**
     * LineProtocol constructor.
     * @param $measurement
     * @param array $fieldSet
     * @param array $tagSet
     * @param \DateTime $time
     */
    public function __construct($measurement, array $fieldSet, array $tagSet = [], \DateTime $time = null)
    {
        $this->measurement = $measurement;
        $this->fieldSet = $fieldSet;
        $this->tagSet = $tagSet;
        $this->time = $time;
    }

    /**
     * @return string
     */
    protected function getEscapedMeasurement()
    {
        $measurement = trim($this->measurement, ' "\'');
        $finalString = str_replace([',', '='], ['\,', '\='], $measurement);
        return $finalString;
    }

    /**
     * @return string
     */
    protected function getEscapedFieldSet()
    {
        $escapedFiledSet = [];
        foreach ($this->fieldSet as $fieldKey => $fieldValue) {
            $escapedFiledSet[] = $this->escapeFieldKey($fieldKey) . '=' . $this->escapeFieldValue($fieldValue);
        }
        ksort($escapedFiledSet);
        return implode(',', $escapedFiledSet);
    }

    /**
     * @param $fieldKey
     * @return string
     */
    protected function escapeFieldKey($fieldKey)
    {
        $fieldKey = trim($fieldKey, ' "\'');
        $finalString = str_replace([',', '=', ' '], ['\,', '\=', '\ '], $fieldKey);
        return $finalString;
    }

    /**
     * @param $fieldValue
     * @return string
     */
    protected function escapeFieldValue($fieldValue)
    {
        if (!is_scalar($fieldValue)) {
            return '';
        }
        if (is_bool($fieldValue)) {
            return $fieldValue ? 'true' : 'false';
        }
        if (is_int($fieldValue) or is_float($fieldValue))
        {
            return $fieldValue;
        }
        return '"' . str_replace(['"'], ['\"'], $fieldValue) . '"';
    }

    /**
     * @return string
     */
    protected function getEscapedTagSet()
    {
        if (empty($this->tagSet)) {
            return '';
        }
        $escapedTagSet = [];
        foreach ($this->tagSet as $tagKey => $tagValue) {
            $escapedTagSet[] = $this->escapeTagKey($tagKey) . '=' . $this->escapeTagValue($tagValue);
        }
        ksort($escapedTagSet);
        return implode(',', $escapedTagSet);
    }

    /**
     * @param $tagKey
     * @return string
     */
    protected function escapeTagKey($tagKey)
    {
        $tagKey = trim($tagKey, ' "\'');
        $finalString = str_replace([',', '=', ' '], ['\,', '\=', '\ '], $tagKey);
        return $finalString;
    }

    /**
     * @param $tagValue
     * @return string
     */
    protected function escapeTagValue($tagValue)
    {
        $tagValue = trim($tagValue, ' "\'');
        $finalString = str_replace([',', '=', ' '], ['\,', '\=', '\ '], $tagValue);
        return $finalString;
    }

    /**
     * @return string
     */
    protected function getEscapedTimestamp()
    {
        return $this->time->getTimestamp() . '000000000';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLineProtocol();
    }

    /**
     * @return string
     */
    public function getLineProtocol()
    {
        $measurementString = $this->getEscapedMeasurement();
        $tagString = $this->getEscapedTagSet();
        $fieldString = $this->getEscapedFieldSet();
        $timestampString = $this->getEscapedTimestamp();

        $finalString = $measurementString;
        if ($tagString) {
            $finalString .= ',' . $tagString;
        }
        $finalString .= ' ' . $fieldString;
        if ($timestampString) {
            $finalString .= ' ' . $timestampString;
        }
        return $finalString;
    }
}
