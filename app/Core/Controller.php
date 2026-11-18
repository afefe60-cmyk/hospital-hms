<?php

namespace App\Core;

abstract class Controller
{
    /**
     * Render a view template
     */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        return View::render($view, $data, $layout);
    }

    /**
     * Return a JSON response
     */
    protected function json(mixed $data, int $statusCode = 200, array $headers = []): Response
    {
        $response = new Response();
        return $response->json($data, $statusCode, $headers);
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        $response = new Response();
        $response->redirect($url, $statusCode);
    }

    /**
     * Basic validation helper
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $fieldRules) {
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && (is_null($value) || $value === '')) {
                    $errors[$field][] = "الحقل {$field} مطلوب.";
                } elseif (str_starts_with($rule, 'min:') && strlen((string)$value) < (int)substr($rule, 4)) {
                    $min = substr($rule, 4);
                    $errors[$field][] = "يجب ألا يقل الحقل {$field} عن {$min} محارف.";
                } elseif (str_starts_with($rule, 'max:') && strlen((string)$value) > (int)substr($rule, 4)) {
                    $max = substr($rule, 4);
                    $errors[$field][] = "يجب ألا يتجاوز الحقل {$field} {$max} محرفاً.";
                } elseif ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "صيغة البريد الإلكتروني غير صحيحة.";
                } elseif ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
                    $errors[$field][] = "يجب أن تكون قيمة {$field} رقمية.";
                }
            }
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('_old_input', $data);
            return [
                'isValid' => false,
                'errors' => $errors
            ];
        }

        return [
            'isValid' => true,
            'data' => $data
        ];
    }
}
