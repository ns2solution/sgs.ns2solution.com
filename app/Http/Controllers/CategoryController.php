<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Validator;
use Exception;

use App\Category;

class CategoryController extends Controller
{
    public function get(Request $request, $id = null)
    {
        // return $request->all();
        try {

            if ($id) {

                if (isset($request['parent'])) {

                    // return "masuk sini";
                    $category = Category::where('parent_id', $request->id)->get();

                } else {

                    $category = Category::where('id', $request->id)->get();

                }

            } else {

            	if (isset($request['parent'])) {
                    // return "masuk sini";
                    $category = Category::where('parent_id', 0)->get();

                } else {

                    $category = Category::all();;

                }

            }

            foreach($category as &$cat){
                if (!empty($cat->category_image)) {
                    $cat->category_image = url('') . '/' . $cat->category_image;
                }
            }

            return response()->json([
                'message' => 'Data Berhasil Diambil.',
                'data'    => $category
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
        
    }

    public function getAll(Request $request)
    {
        try {

            $category = Category::all();

            return response()->json([
                'message' => 'Data Berhasil Diambil.',
                'data'    => $category
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function get_sub_category(Request $request, $id = null)
    {
        // return $request->all();
        try {

            $category = Category::where('parent_id', $request->id)->get();

            return response()->json([
                'message' => 'Data Berhasil Diambil.',
                'data'    => $category
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getTree(Request $request, $id = null)
    {
        try {

            if ($id) {
                $category = Category::where('id', $request->id);
            } else {
                $category = Category::select('id', 'category_name', 'parent_id');
            }

            // dd($category);

            return response()->json([
                'message' => 'Data Berhasil Diambil.',
                'data'    => $this->buildTree($category)
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        $rules = [
            'category_name' => 'required|min:3|max:30',
        ];

        // $rules['category_image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:1024';


        try {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }


            $category = new Category();
            $category->category_name = $request->category_name;
            $category->parent_id = $request->parent_id == '' ? 0 : $request->parent_id;
            $category->category_image = $request->file('category_image') ? _uploadFile($request->file('category_image'), 'category_image') : null;
            // var_dump($request->all());exit();
            $category->save();

            return response()->json([
                'message' => 'Berhasil Tambah Kategori',
                'data'    => $request->all()
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'category_name' => 'required|min:3|max:30'
        ];

        try {

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => ucfirst($validator->errors()->first())
                ], 200);
            }

            $category = Category::find($id);
            $category->category_name = $request->category_name;
            if($request->file('category_image')){
                $category->category_image = $request->file('category_image') ? _uploadFile($request->file('category_image'), 'category_image') : null;
            }
            $category->update();

            return response()->json([
                'message' => 'Berhasil Ubah Kategori',
                'data'    => $request->all(),
                'status' => true
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function delete(Request $request, $id)
    {

        // dd($id);

        try {

            $category = Category::find($id);
            $category->delete();

            return response()->json([
                'message' => 'Berhasil Hapus Kategori',
                'data'    => $request->all()
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function buildTree(&$elements, $parentId = 0)
    {

        $branch = array();


        foreach ($elements as &$element) {

            if ($element->parent_id == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[$element->id] = $element;
                unset($element);
            }
        }
        return $branch;
    }
}
