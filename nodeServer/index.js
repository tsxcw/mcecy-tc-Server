/*
 * @Author: tushan
 * @Date: 2021-12-12 00:10:49
 * @LastEditTime: 2022-01-07 22:27:11
 * @Description: html xss解析
 * @FilePath: /admin/nodeServer/index.js
 */
const xss = require("xss");
const express = require("express")
let bodyParser = require('body-parser')
let jsonParser = bodyParser.json({ limit: '100mb' });
let urlencodedParser = bodyParser.urlencoded({ extended: true, limit: '100mb' });
const {exec,sql,init} = require("mysqls");
const app = express();
app.use(jsonParser);
app.use(urlencodedParser);
// app.use(express.urlencoded({ limit: '50mb' }));
app.post("/xss", (req, res, next) => {
    if (req.body.xss) {
        const text = remove_xss(req.body.xss);
        res.status(200).send({ status: 1, content: text });
    } else {
        res.status(404).send({ status: 0, msg: '没有内容' });
    }

})
app.get("/", (req, res) => {
    res.status(200).send("Runing!")
})
app.listen(90, function (e) {
    console.log("run start port:" + 9900);
});
/**
 * @description: html xss过滤
 * @param {*}
 * @return {*}
 */
const remove_xss = (html = false) => {
    //允许通过的标签
    const html_tag = ['p', 'a', 'img', 'font', 'span', 'b', 'blockquote', 'code', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'br', 's', 'i', 'u', 'strike', 'div', 'strong','pre'];
    //允许使用的属性
    const canuse_attr = ['color', 'size', 'style', 'href', 'src'];
    let dict = {};
    for (let index = 0; index < html_tag.length; index++) {
        //生成参数配置
        const element = html_tag[index];
        dict[element] = canuse_attr;
    }
    return xss(html, {
        whiteList: dict
    })
}