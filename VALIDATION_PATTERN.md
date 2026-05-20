# Validation Pattern - FormRequest

## Overview

Il sistema di validazione centralizzato utilizza un pattern simile a Laravel FormRequest che standardizza e semplifica la validazione degli input in tutti gli endpoint API.

## Struttura

```
src/Requests/
├── FormRequest.php                      # Classe base per tutti i FormRequest
├── ValidationException.php              # Eccezione per errori di validazione
├── Athletes/
│   └── AddAthleteRequest.php            # Validazione per aggiunta atleti
├── Courses/
│   ├── AddCourseRequest.php             # Validazione per aggiunta corsi
│   └── UpdateCourseRequest.php          # Validazione per modifica corsi
├── Disciplines/
│   ├── AddDisciplineRequest.php         # Validazione per aggiunta discipline
│   └── UpdateDisciplineRequest.php      # Validazione per modifica discipline
├── DocumentTypes/
│   ├── AddDocumentTypeRequest.php       # Validazione per aggiunta tipi documento
│   └── UpdateDocumentTypeRequest.php    # Validazione per modifica tipi documento
└── Sites/
    ├── AddSiteRequest.php               # Validazione per aggiunta sedi
    └── UpdateSiteRequest.php            # Validazione per modifica sedi
```

## Regole di Validazione

### Regole Base

- `required` - Campo obbligatorio
- `string` - Deve essere una stringa
- `int` - Deve essere un numero intero
- `float` - Deve essere un numero decimale
- `email` - Deve essere un email valido
- `date` - Deve essere una data valida (YYYY-MM-DD)
- `array` - Deve essere un array
- `nullable` - Campo opzionale

### Regole con Parametri

- `min:N` - Minimo N caratteri (string) o valore (numero)
- `max:N` - Massimo N caratteri (string) o valore (numero)
- `in:value1,value2,value3` - Uno dei valori elencati
- `regex:pattern` - Corrisponde al pattern regex

## Utilizzo negli Endpoint

### Esempio: Aggiunta Sede (public/api/sedi.php)

#### Prima (senza validazione centralizzata)
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $code = trim((string) ($_POST['code'] ?? ''));

    if ($name !== '') {
        $sedi->addSite($name, $code);
    }

    header('Location: /seiryokukai_php/public/index.php?page=sedi');
    exit;
}
```

#### Dopo (con FormRequest)
```php
use App\Requests\Sites\AddSiteRequest;
use App\Requests\ValidationException;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $request = new AddSiteRequest($_POST);
        
        $sedi->addSite(
            $request->getString('name'),
            $request->getString('code')
        );
        
        header('Location: /seiryokukai_php/public/index.php?page=sedi&ok=Sede%20creata%20con%20successo');
        exit;
    } catch (ValidationException $e) {
        handle_validation_errors(
            $e->errors(),
            'sedi',
            [
                'add_name' => $_POST['name'] ?? '',
                'add_code' => $_POST['code'] ?? '',
            ]
        );
    }
}
```

## Creare un Nuovo FormRequest

### Step 1: Creare la classe

```php
<?php

declare(strict_types=1);

namespace App\Requests;

class AddMyEntityRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'field1' => 'required|string|min:1|max:255',
            'field2' => 'required|int|min:1',
            'field3' => 'nullable|email',
            'field4' => 'nullable|date',
        ];
    }

    protected function messages(): array
    {
        return [
            'field1.required' => 'Campo1 è obbligatorio',
            'field1.max' => 'Campo1 non deve superare 255 caratteri',
            'field2.required' => 'Campo2 è obbligatorio',
        ];
    }
}
```

### Step 2: Usare nel endpoint

```php
try {
    $request = new AddMyEntityRequest($_POST);
    
    // Accedi ai dati validati
    $field1 = $request->getString('field1');
    $field2 = $request->getInt('field2');
    $field3 = $request->getString('field3', '');
    $field4 = $request->getString('field4', '');
    
    // Procedi con la logica
    $service->add($field1, $field2);
    
} catch (ValidationException $e) {
    handle_validation_errors($e->errors(), 'pagina', [
        'add_field1' => $_POST['field1'] ?? '',
    ]);
}
```

## Metodi Disponibili in FormRequest

### Getter Tipati

- `getString(string $field, string $default = ''): string` - Restituisce stringa trimmed
- `getInt(string $field, int $default = 0): int` - Restituisce intero
- `getFloat(string $field, float $default = 0.0): float` - Restituisce float
- `getArray(string $field, array $default = []): array` - Restituisce array
- `getBool(string $field, bool $default = false): bool` - Restituisce boolean
- `get(string $field, mixed $default = null): mixed` - Restituisce valore raw

### Metodi di Debug

- `all(): array` - Restituisce tutti i dati validati
- `errors(): array` - Restituisce array di errori
- `hasErrors(): bool` - Verifica se ci sono errori

## Helper Functions in src/lib/data.php

### handle_validation_errors()

Gestisce errori di validazione con redirect HTML:

```php
try {
    $request = new AddSiteRequest($_POST);
    // ...
} catch (ValidationException $e) {
    handle_validation_errors(
        $e->errors(),
        'sedi',
        ['add_name' => $_POST['name'] ?? '']
    );
}
```

### handle_validation_errors_json()

Gestisce errori di validazione con risposta JSON per AJAX:

```php
try {
    $request = new AddSiteRequest($_POST);
    // ...
} catch (ValidationException $e) {
    handle_validation_errors_json($e->errors(), 400);
}
```

## Vantaggi

✅ **Centralizzazione** - Tutte le validazioni in un unico posto  
✅ **Riutilizzabilità** - Stesse regole ovunque needed  
✅ **Consistenza** - Messaggi di errore uniformi  
✅ **Manutenibilità** - Facile modificare regole  
✅ **Type Safety** - Getter tipati per evitare errori  
✅ **Estendibilità** - Facile aggiungere validazioni custom  

## Prossimi Passi

1. Applicare il pattern a tutti gli endpoint API
2. Aggiungere più validatori custom se necessari
3. Creare FormRequest per utenti (complesso, con validazioni speciali)
4. Integrare nella documentazione API
