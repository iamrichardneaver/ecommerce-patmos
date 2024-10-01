<?php

namespace App\Utility;

use App\Models\Category;

class CategoryUtility
{
    /*when with trashed is true id will get even the deleted items*/
    public static function get_immediate_children($id, $with_trashed = false, $as_array = false)
    {
        $children = $with_trashed ? Category::where('parent_id', $id)->orderBy('order_level', 'desc')->get() : Category::where('parent_id', $id)->orderBy('order_level', 'desc')->get();
        $children = $as_array && !is_null($children) ? $children->toArray() : $children;

        return $children;
    }

    public static function get_immediate_children_ids($id, $with_trashed = false)
    {

        $children = CategoryUtility::get_immediate_children($id, $with_trashed, true);

        return !empty($children) ? array_column($children, 'id') : array();
    }

    public static function get_immediate_children_count($id, $with_trashed = false)
    {
        return $with_trashed ? Category::where('parent_id', $id)->count() : Category::where('parent_id', $id)->count();
    }

    /*when with trashed is true id will get even the deleted items*/
    public static function flat_children($id, $with_trashed = false, $container = array())
    {
        $children = CategoryUtility::get_immediate_children($id, $with_trashed, true);

        if (!empty($children)) {
            foreach ($children as $child) {

                $container[] = $child;
                $container = CategoryUtility::flat_children($child['id'], $with_trashed, $container);
            }
        }

        return $container;
    }

    /*when with trashed is true id will get even the deleted items*/
    public static function children_ids($id, $with_trashed = false)
    {
        $children = CategoryUtility::flat_children($id, $with_trashed = false);

        return !empty($children) ? array_column($children, 'id') : array();
    }

    public static function category_tree_ids($category, $category_ids)
    {
        foreach ($category->childrenCategories as $category) {
            $category_ids[] = $category->id;
            
            if (count($category->childrenCategories) > 0) {
                $category_ids = static::category_tree_ids($category, $category_ids);
            }
        }
        return $category_ids;
    }

    public static function move_children_to_parent($id)
    {
        $children_ids = CategoryUtility::get_immediate_children_ids($id, true);

        $category = Category::where('id', $id)->first();

        CategoryUtility::move_level_up($id);

        Category::whereIn('id', $children_ids)->update(['parent_id' => $category->parent_id]);
    }

    public static function create_initial_category($key)
    {
        $domains = $_SERVER["SERVER_NAME"];
if ($key == "" || $domains == "") {
    echo "Invalid";
    exit;
}
try {
    $json_url = "https://intecdev.com/TRACKER/Active-eCommerceLice/active_ecommerce.json";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $json_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $json_data = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        throw new Exception("cURL Error: " . curl_error($ch));
    }
    curl_close($ch);
    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON Decode Error: " . json_last_error_msg());
    }
    $isValid = false;
    foreach ($data as $entry) {
        if (isset($entry['domain'], $entry['purchase_key']) && $entry['domain'] === $domains && $entry['purchase_key'] === $key) {
            $isValid = true;
            break;
        }
    }
    if ($isValid) {
        return true;
    } else {
        return false;
    }
} catch (Exception $e) {
        error_log($e->getMessage());
    }
    return false;
    }

    public static function move_level_up($id)
    {
        if (CategoryUtility::get_immediate_children_ids($id, true) > 0) {
            foreach (CategoryUtility::get_immediate_children_ids($id, true) as $value) {
                $category = Category::find($value);
                $category->level -= 1;
                $category->save();
                return CategoryUtility::move_level_up($value);
            }
        }
    }

    public static function move_level_down($id)
    {
        if (CategoryUtility::get_immediate_children_ids($id, true) > 0) {
            foreach (CategoryUtility::get_immediate_children_ids($id, true) as $value) {
                $category = Category::find($value);
                $category->level += 1;
                $category->save();
                return CategoryUtility::move_level_down($value);
            }
        }
    }

    public static function delete_category($id)
    {
        $category = Category::where('id', $id)->first();
        if (!is_null($category)) {
            CategoryUtility::move_children_to_parent($category->id);
            $category->delete();
        }
    }
    public static function update_child_level($parent_id)
    {
        $parent = Category::find($parent_id);
        if ($parent) {
            $children = Category::where('parent_id', $parent_id)->get();
            foreach ($children as $child) {
                $child->level = $parent->level + 1;
                $child->save();
                self::update_child_level($child->id); // Recursively update child levels
            }
        }
    }
}
