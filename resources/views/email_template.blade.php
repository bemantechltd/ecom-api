<html>
    <head>
        <style type="text/css">
            .mail_template{
                margin: 0 auto;
                padding: 7% auto;
                background-color: #f7f7f7;
                text-align: center;
                margin-bottom: 5px;
            }
            .container{
                display: inline-block;
                border: 1px solid #eee;
                background-color: #ffffff;        
                margin: 15px;
                width: 600px;
                text-align: left;
                border-radius: 5px;        
            }
            .header,.main_body,.footer{
                display: block;
                padding: 15px;
            }
            .header{        
                border-bottom: 1px solid #eee;
                border-radius: 5px 5px 0 0;
            }
            .header > img{
                width: 120px;
                height: auto;
                object-fit: contain
            }
            .footer{
                padding: 10px 15px;
                border-top: 1px solid #eee;
                background-color: #eee;
                border-radius: 0 0 5px 5px;
            }
            .footer p{
                margin: 5px 0; padding: 0;
                font-size: 11px
            }
            .footer .info{
                height: 65px;
            }
            .footer .info div{
                float: left;
                width: 110px;
                height: 40px;
                margin: 0 5px;
                font-size: 12px;
                text-align: left;
            }
            /*.footer .info div > img{
                margin-top: 5px;
                width: 100%;
                height: 100%;
                object-fit: contain
            }*/
            .footer .info div > p{
                margin: 0; padding: 0;
            }
            .unsubscribe_link_block{
                display: inline-block;
                margin-bottom: 15px; font-size: 10px;
                width: 600px;        
            }
            .clearfix::after {
                display: block;
                clear: both;
                content: "";
            }
        </style>
    </head>
    <body>
        <div class="mail_template">
            <div class="container">
                <div class="header">
                    <img src="{{ config('global.http_protocol') }}://{{ urldecode(config('global.domain_url')) }}/images/logo.png" />
                </div>
                <div class="main_body">
                    {!! $html !!}
                    <br><br>Thank You,<br>{{ config('global.domain_title') }}
                </div>
                <div class="footer">
                    <div class="info clearfix">
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>