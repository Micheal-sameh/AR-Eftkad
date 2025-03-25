<?php

namespace App\DTOs;

use App\Attributes\HasEmptyPlaceholders;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

abstract class DTO implements Arrayable
{
    /** string palceholder used for indentifying if a property is empty */
    public const STRING = 'DTO_default_empty_value';

    /** integer palceholder used for indentifying if a property is empty */
    public const INT = PHP_INT_MIN;

    /** float palceholder used for indentifying if a property is empty */
    public const FLOAT = PHP_FLOAT_MIN;

    /** array palceholder used for indentifying if a property is empty */
    public const ARRAY = ['DTO_default_empty_value'];

    /** date palceholder used for indentifying if a property is empty */
    public const DATE = '1970-07-21';

    protected $empty_fields = [];

    public function __construct($properties)
    {
        foreach ($this->getPublicProperties() as $property) {
            $value = $properties[$property->name];

            if ($property->getType()->getName() == Carbon::class
                && ! $this->CheckIfValueIsEmptyPlaceholder($value)
            ) {
                $value = Carbon::parse($value);
            }

            if ($this->CheckIfValueIsEmptyPlaceholder($value)) {
                $this->empty_fields[$property->name] = true;
                $this->{$property->name} = null;
            } else {
                $this->{$property->name} = $value;
            }
        }
    }

    /**
     * checks if a property has been set or not
     */
    public function has($property)
    {
        $dto = new \ReflectionClass(static::class);

        if (empty($dto->getAttributes(HasEmptyPlaceholders::class))) {
            return ! is_null($this->{$property});
        }

        return is_null($this->{$property})
            ? ! array_key_exists($property, $this->empty_fields)
            : true;
    }

    /**
     * return a copy of the instance without one or more properties
     */
    public function except(string|array $property): static
    {
        $clone = clone $this;

        foreach (Arr::wrap($property) as $property_name) {
            $clone->empty_fields[$property_name] = true;
            $clone->{$property_name} = null;
        }

        return $clone;
    }

    /**
     * return a copy of the instance with only the specified properties
     */
    public function only(array $property_names): static
    {
        $clone = clone $this;

        $this->getPublicProperties()
            ->filter(fn (\ReflectionProperty $property) => ! in_array($property->name, $property_names))
            ->each(function (\ReflectionProperty $property) use (&$clone) {
                $clone->empty_fields[$property->name] = true;
                $clone->{$property->name} = null;
            });

        return $clone;
    }

    public function toArray(?array $only = null)
    {
        $array = [];

        $properties = $this->getPublicProperties();

        foreach ($properties as $property) {
            $value = $array[$property->name] = $this->{$property->name};
            if (! is_null($value)) {
                unset($this->empty_fields[$property->name]);
            }
        }

        if ($only) {
            $array = array_intersect_key($array, array_flip($only));
        } else {
            $array = array_diff_key($array, array_filter($this->empty_fields));
        }

        return $array;
    }

    protected function getPublicProperties()
    {
        $reflection_class = new \ReflectionClass($this);
        $public_properties = $reflection_class->getProperties(\ReflectionProperty::IS_PUBLIC);

        return collect($public_properties)
            ->filter(fn (\ReflectionProperty $p) => ! $p->isStatic())
            ->values();
    }

    public function whereNotNull(): array
    {
        $properties = $this->toArray();

        return array_filter($properties, function ($value) {
            return $value !== null;
        });
    }

    protected function CheckIfValueIsEmptyPlaceholder($value)
    {
        $placeholder = $this->getPlaceholder($value);

        if ($value == $placeholder) {
            return true;
        }

        return false;
    }

    protected function getPlaceholder($property_value)
    {
        return match (gettype($property_value)) {
            'integer' => self::INT,
            'double', 'float' => self::FLOAT,
            'string' => self::STRING,
            'array' => self::ARRAY,
            'object' => $property_value instanceof Carbon ? Carbon::parse(self::DATE) : new NullObject,
            default => 'none',
        };
    }

    /**
     * get array of names for the parameters of the constructor of the current class
     *
     * @return array<string>
     */
    protected function getParameterList(): array
    {
        $constructor = new \ReflectionMethod(static::class, '__construct');

        return collect($constructor->getParameters())
            ->map(fn ($p) => $p->name)->toArray();
    }
}
