#!/usr/bin/env node

import gm from 'gm';

gm('header.png')
    .resize(8, 8, '!')
    .noProfile()
    .toBuffer('GIF', function (error, buffer) {
        console.log('data:image/gif;base64,' + buffer.toString('base64'));
    });
