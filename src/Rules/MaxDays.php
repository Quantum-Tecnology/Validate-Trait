<?php

declare(strict_types = 1);

namespace QuantumTecnology\ValidateTrait\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxDays implements ValidationRule
{
    protected string $compareColumn;
    protected int $maxDays;

    /**
     * Create a new rule instance.
     *
     * @param string $compareColumn Nome da coluna para comparar
     * @param int    $maxDays       Número máximo de dias permitidos
     */
    public function __construct(string $compareColumn, int $maxDays)
    {
        $this->compareColumn = $compareColumn;
        $this->maxDays       = $maxDays;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Obter o valor da coluna de comparação
        $compareValue = request()->input($this->compareColumn);

        // Verificar se ambos os valores são datas válidas
        if (!strtotime($value) || ($compareValue && !strtotime($compareValue))) {
            $fail(__('The :attribute must be a valid date.'));

            return;
        }

        if (!$compareValue) {
            $today = now()->toDateString();

            if (strtotime($value) < strtotime($today)) {
                $compareValue = Carbon::parse($value)->addDays($this->maxDays)->toDateString();
            } else {
                $compareValue = Carbon::parse($value)->subDays($this->maxDays)->toDateString();
            }

            request()->merge([
                $this->compareColumn => $compareValue,
            ]);
        }

        // Calcular a diferença em dias
        $date1      = Carbon::parse($value);
        $date2      = Carbon::parse($compareValue);
        $diffInDays = $date1->diffInDays($date2);

        // Verificar se a diferença está dentro do limite
        if ($diffInDays > $this->maxDays) {
            $fail(__('The :attribute must not differ from :compareColumn by more than :maxDays days.', [
                'attribute'     => $attribute,
                'compareColumn' => $this->compareColumn,
                'maxDays'       => $this->maxDays,
            ]));
        }
    }
}
