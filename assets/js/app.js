(function(angular) {
    'use strict';
    var app = angular.module('testApp', []);

    app.service('fileService', ['$http', function ($http) {
        return {
            postProduct: function (product) {
                return $http.post('/product', product);
            },
            getProducts: function () {
                return $http.get('/product');
            },
            removeProduct: function (id) {
                return $http.delete('/product/' + id);
            },
            updateProduct: function (product) {
                return $http.put('/product/' + product.id, product);
            }
        };
    }]);

    var POSITIVE_REGEXP = /^\+?\d+(?:\.\d+)?$/;
    app.directive('positiveNum', function() {
        return {
            require: 'ngModel',
            link: function(scope, elm, attrs, ctrl) {
                ctrl.$validators.positiveNum = function(modelValue, viewValue) {
                    if (ctrl.$isEmpty(modelValue)) {
                        return true;
                    }

                    return !!POSITIVE_REGEXP.test(viewValue);
                };
            }
        };
    });

    app.directive('keyEnter', function () {
        var ENTER_KEY = 13;
        return function (scope, elem, attrs) {
            elem.bind('keydown', function (event) {
                if (event.keyCode === ENTER_KEY) {
                    scope.$apply(attrs.keyEnter);
                }
            });
        };
    });

    app.directive('keyEscape', function () {
        var ESCAPE_KEY = 27;
        return function (scope, elem, attrs) {
            elem.bind('keydown', function (event) {
                if (event.keyCode === ESCAPE_KEY) {
                    scope.$apply(attrs.keyEscape);
                }
            });
        };
    });

    app.directive('eventFocus', ['$timeout', function($timeout) {
        return function(scope, elem, attr) {
            elem.on(attr.eventFocus, function() {
                $timeout(function() {
                    var element = document.getElementById(attr.eventFocusId);
                    if(element) {
                        element.focus();
                    }
                }, 0);
            });

            // Removes bound events in the element itself
            // when the scope is destroyed
            scope.$on('$destroy', function() {
                elem.off(attr.eventFocus);
            });
        };
    }]);

    app.controller('TestController', ['$scope', '$http', 'fileService',
        function ($scope, $http, fileService) {
            $scope.products = [];
            $scope.newProduct = {};
            var originalProducts = {};

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
                    error(function(resp) {
                        console.log(resp);
                    })
            };

            $scope.toggleEdit = function(product) {
                if (product.editing) {
                    fileService.updateProduct(product).
                        error(function(resp) {
                            $scope.cancelEdit(product);
                            console.log(resp);
                        });
                } else {
                    originalProducts[product.id] = angular.copy(product);
                }

                product.editing = !product.editing;
            };

            $scope.cancelEdit = function(product) {
                var originalProduct = originalProducts[product.id];
                if (originalProduct) {
                    product.name = originalProduct.name;
                    product.quantity = originalProduct.quantity;
                    product.price = originalProduct.price;
                }
                product.editing = false;
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
                return (field.$error.positiveNum ||field.$error.required || field.$error.number || field.$error.min) && field.$dirty;
            };
        }]);
})(angular);