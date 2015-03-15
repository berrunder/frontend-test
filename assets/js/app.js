(function(angular) {
    'use strict';
    var app = angular.module('testApp', []);

    app.service('fileService', ['$http', function($http) {
        var postProduct = function(product) {
            return $http.post('/product', product);
        };

        var getProducts = function() {
            return $http.get('/product');
        };

        var removeProduct = function(id) {
            return $http.delete('/product/' + id);
        };

        return {
            postProduct: postProduct,
            getProducts: getProducts,
            removeProduct: removeProduct
        };
    }]);

    app.controller('TestController', ['$scope', '$http', 'fileService',
        function ($scope, $http, fileService) {
            $scope.products = [];
            $scope.newProduct = {};

            fileService.getProducts().
                success(function(data) {
                    if (data && data.length) {
                        $scope.products = $scope.products.concat(data);
                    }
                });


            $scope.submitProduct = function() {
                var product = angular.copy($scope.newProduct);
                fileService.postProduct(product).
                    success(function(data) {
                        if (data.success) {
                            product.id = data.id;
                            product.submitted = data.submitted;
                            $scope.products.push(product);
                            $scope.newProduct.name = '';
                            $scope.newProduct.quantity = '';
                            $scope.newProduct.price = '';
                            $scope.productForm.$setPristine();
                        } else {
                            console.log(data);
                        }
                    }).
                    error(function(data) {
                        console.log(data);
                    })
            };

            $scope.deleteProduct = function(product) {
                var index = $scope.products.indexOf(product);
                if (index >= 0) {
                    fileService.removeProduct(product.id);
                    $scope.products.splice(index, 1);
                }
            };

            $scope.getTotalPrice = function() {
                return $scope.products.reduce(function(sum, currentItem) {
                    return sum + currentItem.price * currentItem.quantity;
                }, 0);
            };

            $scope.invalidNum = function(field) {
                return (field.$error.required || field.$error.number) && field.$dirty;
            };
        }]);
})(angular);