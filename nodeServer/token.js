/*
 * @Author: tushan
 * @Date: 2021-12-12 00:10:49
 * @LastEditTime: 2022-01-05 13:32:27
 * @Description: cdn鉴权
 * @FilePath: /admin/nodeServer/token.js
 */
const xss = require("xss");
const express = require("express")
let bodyParser = require('body-parser')
let jsonParser = bodyParser.json({ limit: '100mb' });
let urlencodedParser = bodyParser.urlencoded({ extended: true, limit: '100mb' });
const app = express();
app.use(jsonParser);
app.use(urlencodedParser);
const sizeArr = [240, 350, 700, 900];
const size = `(?<=imageMogr2\/thumbnail\/)(${sizeArr.join("|")})(?=x$|x\/)`;
const regx = RegExp(size, "i");
app.get("/*", (req, res) => {
    try {
        const params = req._parsedUrl.query;//获取url参数
        const REX = /imageMogr2\/thumbnail/;
        // if (REX.test(params)) {//匹配是否否和条件
            // if (regx.test(params)) {
                res.status(200).send("Runing!")
            // }
        // }
    } catch (error) {
    }
    try {
        res.sendStatus(404);//上述都不满足则返回404，不予显示图片
    } catch (error) {

    }
})
app.listen(9999, function (e) {
    console.log("run start port:" + 9900);
});

