<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tymon\JWTAuth\Claims;

use Illuminate\Support\Str;
use Illuminate\Support\Collection as IlluminateCollection;

class Collection extends IlluminateCollection
{
    /**
     * {@inheritdoc}
     */
    public function __construct($items = [])
    {
        parent::__construct($this->sanitizeClaims($items));
    }

    /**
     * Ensure that the given claims array is keyed by the claim name.
     *
     * @param  mixed  $items
     *
     * @return array
     */
    private function sanitizeClaims($items)
    {
        if (array_keys($items) === range(0, count($items) - 1)) {
            $claims = [];
            foreach ($items as $value) {
                $claims[$value->getName()] = $value;
            }

            return $claims;
        }

        return $items;
    }

    /**
     * Get a Claim instance by it's unique name.
     *
     * @param  string  $name
     * @param  callable  $callback
     * @param  mixed  $default
     *
     * @return \Tymon\JWTAuth\Claims\Claim
     */
    public function getByClaimName($name, callable $callback = null, $default = null)
    {
        return $this->filter(function ($claim) use ($name) {
            return $claim->getName() === $name;
        })->first($callback, $default);
    }

    /**
     * Validate each claim under a given context.
     *
     * @param  string  $context
     *
     * @return $this
     */
    public function validate($context = 'payload')
    {
        $this->each(function ($claim) {
            call_user_func_array(
                [$claim, 'validate'.Str::ucfirst($context)],
                array_shift(func_get_args())
            );
        });

        return $this;
    }

    /**
     * Determine of the Collection contains all of the given keys.
     *
     * @param  array  $claims
     *
     * @return bool
     */
    public function hasAllClaims($claims)
    {
        return count(array_diff(array_keys($this->toArray()), $claims)) === 0;
    }

    /**
     * Get the claims as key/val array.
     *
     * @return array
     */
    public function toPlainArray()
    {
        return $this->map(function ($claim) {
            return $claim->getValue();
        })->toArray();
    }
}