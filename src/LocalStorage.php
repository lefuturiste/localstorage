<?php

namespace Lefuturiste\LocalStorage;

use Carbon\Carbon;
use Carbon\CarbonInterval;

class LocalStorage
{

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $state = [];

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->read();
    }

    private function read(): void
    {
        if (!file_exists($this->path)) {
            $this->state = [];
        } else {
            $contents = json_decode(file_get_contents($this->path), true);
            $this->state = $contents == NULL ? [] : $contents;
        }
    }

    public function write(): void
    {
        file_put_contents($this->path, json_encode($this->state));
    }

    public function save(): void
    {
        $this->write();
    }

    public function persist(): void
    {
        $this->write();
    }

    public function getState(): array
    {
        return $this->state;
    }

    public function getAll(): array
    {
        return array_map(function ($item) {
            return $item['value'];
        }, $this->state);
    }

    public function exist(string $key): bool
    {
        return isset($this->state[$key]) ? true : false;
    }

    public function get(string $key)
    {
        return isset($this->state[$key]) ? $this->state[$key]['value'] : NULL;
    }

    public function set(string $key, $value, $withDate = true): void
    {
        $this->state[$key] = [
            'value' => $value
        ];
        if ($withDate) {
            $this->state[$key]['at'] = (new Carbon())->toDateTimeString();
        }
    }

    public function del(string $key): void
    {
        unset($this->state[$key]);
    }

    public function delete(string $key): void
    {
        $this->del($key);
    }

    public function remove(string $key): void
    {
        $this->del($key);
    }

    public function deleteOlderThan(CarbonInterval $duration): void
    {
        $toCompare = new Carbon();
        $toCompare->add($duration->invert());
        foreach ($this->state as $key => $item)
        {
            $dateItem = new Carbon($item['at']);
            if (!$dateItem->greaterThan($toCompare)){
                unset($this->state[$key]);
            }
        }
    }

    public function clear(): void
    {
        $this->state = [];
    }

    public function reset(): void
    {
        $this->clear();
    }

    public function isEmpty(): bool
    {
        return empty($this->state);
    }

    public function getCount(): int
    {
        return count($this->state);
    }

    public function getCreationDateTime(string $key)
    {
        return isset($this->state[$key]) ? $this->state[$key]['at'] : NULL;
    }
}
