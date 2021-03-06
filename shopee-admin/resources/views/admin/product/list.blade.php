@extends('admin.main')

@section('content')
    <table class="table">
        <thead>
        <tr>
            <th style="width: 100px"> ID </th>
            <th> Tên sản phẩm </th>
            <th> Ảnh </th>
            <th> Danh mục </th>
            <th> Giá gốc (VND)</th>
            <th> Giá khuyến mãi (VND)</th>
            <th> Active </th>
            <th style="width: 100px"> &nbsp; </th>
        </tr>
        </thead>
        <tbody>
            @foreach($products as $key => $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td><img src="{{ $product->thumb }}" alt="anh-san-pham" width="100px"></td>
                <td>{{ $product->menu->name }}</td>
                <td>{{ $product->price }}</td>
                <td>{{ $product->price_sale }}</td>
                <td>{!! \App\Helpers\Helper::active($product->active) !!}</td>
                <td>
                    <a class="btn btn-primary btn-sm" href="/admin/products/edit/{{ $product->id }}">
                        <i class="fas fa-edit"></i>
                    </a>

                    <a  href="#" class="btn btn-danger btn-sm"
                        onclick="removeRow({{ $product->id }}, '/admin/products/destroy')">
                        <i class="far fa-trash-alt"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="card-footer clearfix">{!! $products->links() !!}</div>
@endsection


