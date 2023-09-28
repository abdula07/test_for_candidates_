# test_for_candidates_

1) Пример запроса на получение цены, я сделал валидацию taxNumber только для германий поэтому используйте только ее
http://127.0.0.1:80/calculate-price

Пример json тела запроса:

{
    "product": 1,
    "taxNumber": "DE123456789",
    "couponCode": "D15"
}


2) Пример запроса на платеж
http://127.0.0.1:80/purchase

Пример json тела запроса:

{
    "product": 1,
    "taxNumber": "IT12345678900",
    "couponCode": "D15",
    "paymentProcessor": "paypal"
}

пример с использованием curl

1)

curl --location 'http://127.0.0.1:41645/purchase' \
--header 'Content-Type: application/json' \
--data '{
    "product": 1,
    "taxNumber": "DE111111111",
    "couponCode": "12ewe",
    "paymentProcessor": "stripe"
}'

2) 


curl --location 'http://127.0.0.1:41645/calculate-price' \
--header 'Content-Type: application/json' \
--data '{
    "product": 1,
    "taxNumber": "DE111111111",
    "couponCode": "12ewe",
}'
