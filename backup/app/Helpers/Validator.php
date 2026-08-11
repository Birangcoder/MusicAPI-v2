<?php

declare(strict_types=1);

namespace App\Helpers;

class Validator
{
    private array $errors = [];

    public function required(array $data, array $fields): self
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                $this->errors[$field][] = 'The field is required.';
            }
        }

        return $this;
    }

    public function email(array $data, string $field): self
    {
        if (
            isset($data[$field]) &&
            trim((string)$data[$field]) !== '' &&
            !filter_var($data[$field], FILTER_VALIDATE_EMAIL)
        ) {
            $this->errors[$field][] = 'Invalid email.';
        }

        return $this;
    }

    public function min(array $data, string $field, int $length): self
    {
        if (
            isset($data[$field]) &&
            strlen((string)$data[$field]) < $length
        ) {
            $this->errors[$field][] = "Minimum {$length} characters required.";
        }

        return $this;
    }

    public function max(array $data, string $field, int $length): self
    {
        if (
            isset($data[$field]) &&
            strlen((string)$data[$field]) > $length
        ) {
            $this->errors[$field][] = "Maximum {$length} characters allowed.";
        }

        return $this;
    }

    public function integer(array $data, string $field): self
    {
        if (
            isset($data[$field]) &&
            filter_var($data[$field], FILTER_VALIDATE_INT) === false
        ) {
            $this->errors[$field][] = 'Must be an integer.';
        }

        return $this;
    }

    public function boolean(array $data, string $field): self
    {
        if (
            isset($data[$field]) &&
            !in_array($data[$field], [0, 1, true, false, '0', '1'], true)
        ) {
            $this->errors[$field][] = 'Must be boolean.';
        }

        return $this;
    }

    public function in(array $data, string $field, array $values): self
    {
        if (
            isset($data[$field]) &&
            !in_array($data[$field], $values, true)
        ) {
            $this->errors[$field][] = 'Invalid value.';
        }

        return $this;
    }

    public function confirmed(array $data, string $field): self
    {
        $confirm = $field . '_confirmation';

        if (
            isset($data[$field]) &&
            (($data[$confirm] ?? null) !== $data[$field])
        ) {
            $this->errors[$field][] = 'Confirmation does not match.';
        }

        return $this;
    }

    public function nullable(array $data, string $field): bool
    {
        return !isset($data[$field]) || trim((string)$data[$field]) === '';
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validate(): void
    {
        if ($this->fails()) {
            Response::validation($this->errors);
        }
    }
}