<?php
namespace Learnosity\Schemas;

require_once "QuestionData.php";

class SchemaMaker
{
    private array $editorSchemas;
    private array $questionTypes;
    private array $schemas;

    const HIDDEN = ['type', 'preview', 'canvas_container_style', 'default_inputs'];
    const ALLOWED_TYPES = ["object", "boolean", "string", "array", "number", "string/number"];
    const TYPE_FIX_MAP = [
        "groupPossibleResponses" => "boolean",
        "stringUnits" => "string",
        "charmapArray" => "array",
        "imageObject" => "object",
        "textarea" => "string",
        "stringOrderedList" => "string",
        "editorMcqOptionValue" => "string",
        "editor"=> "string",
    ];

    public function __construct($questionTypes = [])
    {
        $this->questionTypes = $questionTypes;
        $questionData = new QuestionData();
        $this->editorSchemas = $questionData->getSchemas($this->questionTypes);
        $this->schemas = [];

        //Support "all" option:
        if (empty($questionTypes)) {
            $this->questionTypes = array_keys($this->editorSchemas);
        }

        foreach ($this->questionTypes as $type) {
            $this->schemas[$type] = [
                "type" => "object",
                "properties" => []
            ];
        }
    }

    public function makeSchema()
    {
        foreach ($this->editorSchemas as $type => $editorSchema) {
            if (in_array($type, $this->questionTypes)) {
                fwrite(STDERR, "Processing: {$type}\n");
                $this->extractProperties($editorSchema, $this->schemas[$type]);
            }
        }
        return $this->schemas;
    }

    private function extractProperties(array $editorSchema, array &$schema)
    {
        $required = [];
        if (isset($editorSchema['attributes'])) {
            $attributes = $editorSchema['attributes'];
        } else {
            $attributes = $editorSchema['conditional_attributes'];
        }
        foreach ($attributes as $property => $value) {
            if (in_array($property, self::HIDDEN) || isset($value['legacy_attribute'])) {
                continue;
            }

            $schema['properties'][$property] = [];
            $this->addExpectedKeys($schema['properties'][$property], $value);
                
            if (isset($value['required']) && $value['required']) {
                $required[] = $property;
            }

            if (isset($value['type'])) {
                if (in_array($value['type'], array_keys(self::TYPE_FIX_MAP))) {
                    $schema['properties'][$property]['type'] = self::TYPE_FIX_MAP[$value['type']];
                    $value['type'] = self::TYPE_FIX_MAP[$value['type']];
                }
                switch ($value['type']) {
                    case "object":
                        $this->extractProperties($value, $schema['properties'][$property]);
                        break;
                    case "array":
                        if (isset($value['items']) && isset($value['items']['type'])) {
                            switch ($value['items']['type']) {
                                case 'string':
                                    $schema['properties'][$property]['items'] = ["type" => "string"];
                                    break;
                                case 'object':
                                    $schema['properties'][$property]['items'] = ["type" => "object"];
                                    $this->extractProperties($value['items'], $schema['properties'][$property]['items']);
                                    break;
                            }
                        }
                        break;
                    case "string/number":
                        $schema['properties'][$property]['items'] = [
                            'oneOf' => [
                                [
                                    'type' => 'string'
                                ],
                                [
                                    'type' => 'number'
                                ]
                            ]
                        ];
                        unset($schema['properties'][$property]['type']);
                        break;
                    case "questionOrderlist":
                    //This is a special case, it has to be mapped to an array, but there's no way to infer the items in that array. Doing it manually instead.
                        $schema['properties'][$property]['type'] = "array";
                        $schema['properties'][$property]['items'] = ["type" => "string"];
                        break;
                }
            }

            if (isset($value['element']) && $value['element'] == "select" && isset($value['options'])) {
                $enum = [];
                foreach ($value['options'] as $option) {
                    $enum[] = is_array($option) ? $option['value'] : $option;
                }
                $schema['properties'][$property]["enum"] = $enum;
            }
        }
        $schema['required'] = $required;
    }

    private function addExpectedKeys(array &$workingProperty, array $value)
    {
        foreach (["description", "type", "default"] as $expectedKey) {
            if (isset($value[$expectedKey])) {
                $workingProperty[$expectedKey] = $value[$expectedKey];
            }
            // else {
            //     throw new \Exception("Could not find expected key: {$expectedKey}");
            // }
        }
    }
}
