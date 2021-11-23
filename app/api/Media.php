<?php
    use Library\Media;
    use Library\MediaTypes;
    use Helper\ApiResponse as Respond;
    use Helper\Request;
    use Helper\Validate;
    use Helper\Header;

    $this->__registerMethod('get', function($params) {
        if (isset($params[0])) {
            $media = new Media;
            $info = $media->info(explode('.', $params[0])[0], false);
            if ($info) {
                if (!$info->public) {
                    $user = $this->user->info();
                    if (!(intval($user->id) == intval($info->owner)) && !$this->user->check('media.get.' . $info->uuid)) {
                        http_response_code(403);
                        Respond::error("private_media", "You are not allowed to access this private media.");
                        return;
                    }
                }

                Header::ContentType(mime_content_type($info->filepath));
                if (isset($_GET['size']) || isset($_GET['width']) || isset($_GET['height'])) {
                    $workableImages = ['avif', 'bmp', 'gd2', 'gd2part', 'gd', 'gif', 'jpg', 'jpeg', 'png', 'string', 'tga', 'wbmp', 'webp', 'xbm', 'xpm'];
                    if (in_array($info->ext, $workableImages)) {
                        $size = 100;
                        list($width, $height) = getimagesize($info->filepath);

                        if (isset($_GET['width']) && $_GET['width'] < $width) $size = intval($_GET['width'] / $width * 100);
                        if (isset($_GET['height']) && $_GET['height'] < $height) $size = intval($_GET['height'] / $height * 100);
                        if (isset($_GET['size'])) $size = $_GET['size'];

                        if ($size < 5) $size = 5;
                        if ($size > 100) $size = 100;
                        if ($size % 5 !== 0) $size = ($size % 5 < 2.5 ? $size - ($size % 5) : $size + (5 - ($size % 5)));

                        if (file_exists($info->path . $info->uuid . '_' . $size . '.' . $info->ext)) {
                            print_r(file_get_contents($info->path . $info->uuid . '_' . $size . '.' . $info->ext));
                        } else {
                            $ext = $info->ext;
                            if ($ext == 'jpg') $ext = 'jpeg';
                            $image = call_user_func('imagecreatefrom' . $ext, $info->filepath);
                            if (!$image) {
                                if (isset($_GET['verbose'])) return Respond::error('image_read_error', "An unexpected error occured while reading the image from the filesystem.");
                                print_r(file_get_contents($info->filepath));
                            } else {
                                $newwidth = $width * ($size / 100);
                                $newheight = $height * ($size / 100);
                                $newimage = imagecreatetruecolor($newwidth, $newheight);
                                $color = imagecolorallocatealpha($newimage, 0, 0, 0, 127);
                                imagefill($newimage, 0, 0, $color);
                                imagesavealpha($newimage, true);
                                //For image transparency, props to: https://stackoverflow.com/a/28027957

                                imagecopyresized($newimage, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                                call_user_func('image' . $ext, $newimage, $info->path . $info->uuid . '_' . $size . '.' . $info->ext);
                                call_user_func('image' . $ext, $newimage);
                            }
                        }
                    } else {
                        if (isset($_GET['verbose'])) return Respond::error('unsupported_format', "The requested media does not support resizing due to an unsupported file format.");
                        print_r(file_get_contents($info->filepath));
                    }
                } else {
                    print_r(file_get_contents($info->filepath));
                }
            } else {
                http_response_code(404);
                Respond::error("unknown_media", "The requested media does not exist. It might have been deleted by it's owner or a site's administrator.");
            }
        } else {
            http_response_code(400);
            Respond::error("missing_identifier", "Did not receive a parameter in the url to identify the media.");
        }
    });

    $this->__registerMethod("create", function($params) {
        if (!Request::requireMethod("post")) die();
        if (!Request::requireAuthentication()) die();

        $media = new Media;
        $postdata = Request::parsePost();
        if (!isset($_FILES['file'])) {
            http_response_code(400);
            Respond::error("missing_file", "Did not receive a file upload with name \"file\".");
        } else if (!isset($postdata->type)) {
            http_response_code(400);
            Respond::error("missing_type", "Did not receive a mediatype through post parameter \"type\".");
        } else {
            $res = $media->create($postdata->type, 'mtdev', $_FILES['file']['tmp_name'], explode('.', $_FILES['file']['name'])[count(explode('.', $_FILES['file']['name'])) - 1]);
            if ($res->success) {
                unlink($_FILES['file']['tmp_name']);
                if (isset($postdata->public) && intval($postdata->public) === 0) $media->makePrivate($res->uuid);
                Respond::success($res);
            } else {
                Respond::error($res->error, $res);
            }
        }
    });