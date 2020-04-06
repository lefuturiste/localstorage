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

    public function write(): self
    {
        file_put_contents($this->path, json_encode($this->state));

        return $this;
    }

    public function save(): self
    {
        return $this->write();
    }

    public function persist(): self
    {
        return $this->write();
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

    public function exists(string $key): bool
    {
        return isset($this->state[$key]) ? true : false;
    }

    public function exist(string $key): bool
    {
        return $this->exists($key);
    }

    public function get(string $key)
    {
        return isset($this->state[$key]) ? $this->state[$key]['value'] : NULL;
    }

    public function set(string $key, $value, $withDate = true): self
    {
        $this->state[$key] = [
            'value' => $value
        ];
        if ($withDate) {
            $this->state[$key]['at'] = (new Carbon())->toDateTimeString();
        }
        
        return $this;
    }

    public function del(string $key): self
    {
        unset($this->state[$key]);

        return $this;
    }

    public function delete(string $key): self
    {
        return $this->del($key);
    }

    public function remove(string $key): self
    {
        return $this->del($key);
    }

    public function deleteOlderThan(CarbonInterval $duration): self
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

        return $this;
    }

    public function clear(): self
    {
        $this->state = [];

        return $this;
    }

    public function reset(): self
    {
        return $this->clear();
    }

    public function isEmpty(): bool
    {
        return empty($this->state);
    }

    /**
     * Delete the json storage file
     */
    public function unlinkStorage(): self
    {
        unlink($this->path);

        return $this;
    }

    public function getCount(): int
    {
        return count($this->state);
    }

    public function getCreationDateTime(string $key)
    {
        return isset($this->state[$key]) ? $this->state[$key]['at'] : NULL;
    }

    public function getPath()
    {
        return $this->path;
    }
}
