<?php

namespace App\Services;
use App\Models\Core\Entity;
use App\Models\Core\FlexField;
use App\Models\Core\FlexValue;

class EntityService
{
    public static function store($data = null)
    {
        // Create a new entity with the type specified in the action.
        $entityField['type'] = 'entity';
        $entity = Entity::create($entityField);

        // Store flex field values for the newly created entity.
        EntityService::storeFlexFieldValue( $entity, $data ?? []);

        return $entity;
    }

    public static function update($entity_id, $data = null)
    {
        $entity = Entity::find($entity_id);

        if ($entity) {
            // Store flex field values for the newly created entity.
            EntityService::storeFlexFieldValue($entity, $data ?? []);

            return $entity;
        }

        return null;
    }

    public static function storeFlexFieldValue($entity, $data)
    {
        FlexValue::where('entity_id', $entity['id'])->delete();

        // Retrieve a list of enabled flex field IDs for the specified entity type.
        $entityFlexFields = FlexField::where('entity_type', 'entity')
            ->where('is_enabled', true)
            ->pluck('id')
            ->all();

        // Extract flex field data from the input, or use an empty array if not present.
        $flexFieldsData = $data['flex_fields'] ?? [];
        $flexValues = null;

        if (count($entityFlexFields) > 0) {
            // Iterate through the enabled flex fields and build a list of values.
            foreach ($entityFlexFields as $value) {
                $flexValues[$value] = $flexFieldsData[$value] ?? '';
            }

            // Create a new FlexValue instance for the entity and store the flex values as JSON.
            $flexValue = new FlexValue();
            $flexValue->create([
                'entity_id' => $entity->id,
                'flex_values' => json_encode($flexValues),
            ]);

            return $flexValue;
        }

        // If no enabled flex fields were found, return null.
        return null;
    }

    public static function belongsTo($model)
    {
        return $model->belongsTo(Entity::class, 'entity_id')->select('id');
    }
}
