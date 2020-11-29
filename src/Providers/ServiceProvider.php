<?php

namespace Jiannei\Enum\Laravel\Providers;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Jiannei\Enum\Laravel\Http\Requests\EnumRequest;
use Jiannei\Enum\Laravel\Http\Requests\Rules\Enum;
use Jiannei\Enum\Laravel\Http\Requests\Rules\EnumKey;
use Jiannei\Enum\Laravel\Http\Requests\Rules\EnumValue;

class ServiceProvider extends IlluminateServiceProvider
{
    public function boot()
    {
        $this->bootValidationRules();
    }

    protected function bootValidationRules(): void
    {
        Validator::extend('enum_key', function (string $attribute, $value, array $parameters, ValidatorContract $validator): bool {
            $enum = $parameters[0] ?? null;
            $strict = $parameters[1] ?? null;

            if (!$strict) {
                return (new EnumKey($enum))->passes($attribute, $value);
            }

            $strict = (bool) json_decode(strtolower($strict));

            return (new EnumKey($enum, $strict))->passes($attribute, $value);
        });

        Validator::extend('enum_value', function (string $attribute, $value, array $parameters, ValidatorContract $validator): bool {
            $enum = $parameters[0] ?? null;
            $strict = $parameters[1] ?? null;

            if (!$strict) {
                return (new EnumValue($enum))->passes($attribute, $value);
            }

            $strict = (bool) json_decode(strtolower($strict));

            return (new EnumValue($enum, $strict))->passes($attribute, $value);
        });

        Validator::extend('enum', function (string $attribute, $value, array $parameters, ValidatorContract $validator): bool {
            $enum = $parameters[0] ?? null;

            return (new Enum($enum))->passes($attribute, $value);
        });
    }

    public function register()
    {
        $this->registerRequestTransformMacro();

        $this->setupConfig();
    }

    protected function registerRequestTransformMacro()
    {
        Request::mixin(new EnumRequest);
    }

    protected function setupConfig()
    {
        $path = dirname(__DIR__, 2).'/config/enum.php';

        $this->mergeConfigFrom($path, 'enum');
    }
}
