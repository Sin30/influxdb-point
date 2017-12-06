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
    protected function getEscapedFields()
    {
        $escapedFiledSet = [];
        foreach ($this->fieldSet as $fieldKey => $fieldValue) {
            $fieldKey = $this->escapeString($fieldKey);
            $fieldValue = $this->escapeFieldValue($fieldValue);
            $escapedFiledSet[$fieldKey] = $fieldKey . '=' . $fieldValue;
        }
        ksort($escapedFiledSet);
        return implode(',', $escapedFiledSet);
    }

    /**
     * @return string
     */
    protected function getEscapedTags()
    {
        if (empty($this->tagSet)) {
            return '';
        }
        $escapedTagSet = [];
        foreach ($this->tagSet as $tagKey => $tagValue) {
            $tagKey = $this->escapeString($tagKey);
            $tagValue = $this->escapeString($tagValue);
            $escapedTagSet[$tagKey] = $tagKey . '=' . $tagValue;
        }
        ksort($escapedTagSet);
        return implode(',', $escapedTagSet);
    }

    /**
     * @param $string
     * @return string
     */
    protected function escapeString($string)
    {
        $string = trim($string, ' "\'');
        $finalString = str_replace([',', '=', ' '], ['\,', '\=', '\ '], $string);
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
        if (is_int($fieldValue) or is_float($fieldValue)) {
            return $fieldValue;
        }
        $fieldValue = trim($fieldValue, ' "\'');
        return '"' . str_replace(['"'], ['\"'], $fieldValue) . '"';
    }

    /**
     * @return string
     */
    protected function getEscapedTimestamp()
    {
        if (is_null($this->time)) {
            return '';
        }
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
        $finalString = $this->escapeString($this->measurement);
        $tags = $this->getEscapedTags();
        if ($tags) {
            $finalString .= ',' . $tags;
        }
        $finalString .= ' ' . $this->getEscapedFields();
        $timestamp = $this->getEscapedTimestamp();
        if ($timestamp) {
            $finalString .= ' ' . $timestamp;
        }
        return $finalString;
    }
}
