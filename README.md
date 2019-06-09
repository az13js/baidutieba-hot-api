# 百度贴吧热门动态获取 API

## 接口地址

    https://tieba.baidu.com/f/index/feedlist

## 请求方式

    GET

## 请求的参数和默认设置

    is_new 等于1
    tag_id 等于all
    limit 等于20
    offset 等于limit的整数倍，默认情况下它应该取值0，20，40以此类推。
    last_tid 默认等于0，后续请求需要设置为返回数据中的last_tid
    _ 这个是当前时间戳，以毫秒为单位的整数

## 请求头设置

    User-Agent: 不同浏览器这里不同
    Referer: https://tieba.baidu.com/index.html
    X-Requested-With: XMLHttpRequest
    Cookie: 不同用户 COOKIE 不同

## 请求举例

    https://tieba.baidu.com/f/index/feedlist?is_new=1&tag_id=all&limit=20&offset=0&last_tid=0

## 返回参数

    no 默认等于0
    error 字符串sucess
    data 一个JSON对象，内容是：
    total 动态总数
    has_more 布尔值，这个请求后面还有没有新的数据
    html 一个字符串，里面是HTML的标签
    last_tid 请求数据里面最后一个帖子的id，发出下一个请求的时候把这个id作为参数中的last_tid传递。

## 返回参数举例

    {
        "no":0，
        "error":"sucess",
        "data":{
            "total":42,
            "has_more":true,
            "html":"很长这里不写出来",
            "last_tid":5927761323
        }
    }