<?php

namespace App\Http\Controllers\Api\Dashboard\Chat;

use App\ChatGroup;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Chat\ChatGroupResource;
use App\Team;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
/**
 * @group Dashboard V4 / Chat
 * APIs for dashboard chat
 */
class IndexController extends Controller
{
    //


    /**
     * Get Contacts
     *
     * @response {
        "Response": true,
        "StatusCode": 200,
        "Message": "contacts",
        "Result": [
            {
                "id": 277,
                "name": null,
                "title": "imgaes updates",
                "picture": "media/chats/groups/Al68HNArH4i3TG6FvFE9EZYL6DfZshitPYyppBaF.jpg",
                "last_message": {
                    "id": 1265,
                    "group_id": 277,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "xcx",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-07-08 17:56:49",
                    "updated_at": "2021-07-08 17:56:49",
                    "msg_identification": "1625767007663",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-08T19:45:28.000000Z"
            },
            {
                "id": 13,
                "name": "Hasnain Ali",
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 1264,
                    "group_id": 13,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "dsds",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-07-08 15:52:47",
                    "updated_at": "2021-07-08 15:52:47",
                    "msg_identification": "1625759566324",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 176,
                        "name": "Hasnain Ali",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 13,
                            "user_id": 176
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T12:47:19.000000Z"
            },
            {
                "id": 271,
                "name": null,
                "title": "group1",
                "picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                "last_message": {
                    "id": 1263,
                    "group_id": 271,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "sds",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-07-08 12:31:14",
                    "updated_at": "2021-07-08 12:31:14",
                    "msg_identification": "1625747474190",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 136,
                        "name": "erik eijgenstein",
                        "profile_picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                        "pivot": {
                            "group_id": 271,
                            "user_id": 136
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 136,
                                "pivot": {
                                    "user_id": 136,
                                    "team_id": 5,
                                    "created_at": "2020-10-31 16:03:25"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 136,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 136,
                                    "position_id": 10
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T10:08:04.000000Z"
            },
            {
                "id": 25,
                "name": "Lionel Messi",
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 1259,
                    "group_id": 25,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "gffv",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-07-08 12:19:07",
                    "updated_at": "2021-07-08 12:19:07",
                    "msg_identification": "1625746747169",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 167,
                        "name": "Lionel Messi",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 25,
                            "user_id": 167
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-24T09:26:16.000000Z"
            },
            {
                "id": 218,
                "name": "Creating new Team",
                "title": "Creating new Team",
                "picture": null,
                "last_message": {
                    "id": 1246,
                    "group_id": 218,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "hh",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-07-08 11:42:45",
                    "updated_at": "2021-07-08 11:42:45",
                    "msg_identification": "1625744565010",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-30T14:26:13.000000Z"
            },
            {
                "id": 8,
                "name": "Fami Sultana,Fatima Sultana,abdul Haseeb,Tariq Sidd,Saad Saleem,tr 2 rerum",
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 1244,
                    "group_id": 8,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "6",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-07-08 10:47:58",
                    "updated_at": "2021-07-08 10:47:58",
                    "msg_identification": "1625741277259",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 6,
                        "name": "Fami Sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 8,
                            "user_id": 6
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 6,
                                "name": "Center Midfield",
                                "position_id": 5,
                                "pivot": {
                                    "user_id": 6,
                                    "position_id": 5
                                }
                            }
                        ]
                    },
                    {
                        "id": 7,
                        "name": "Fatima Sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 8,
                            "user_id": 7
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 9,
                        "name": "abdul Haseeb",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 8,
                            "user_id": 9
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 9,
                                "name": "Right Back",
                                "position_id": 2,
                                "pivot": {
                                    "user_id": 9,
                                    "position_id": 2
                                }
                            }
                        ]
                    },
                    {
                        "id": 10,
                        "name": "Tariq Sidd",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 8,
                            "user_id": 10
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 10,
                                "name": "Right Back",
                                "position_id": 2,
                                "pivot": {
                                    "user_id": 10,
                                    "position_id": 2
                                }
                            }
                        ]
                    },
                    {
                        "id": 11,
                        "name": "Saad Saleem",
                        "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg",
                        "pivot": {
                            "group_id": 8,
                            "user_id": 11
                        },
                        "teams": [
                            {
                                "team_id": 50,
                                "team_name": "Rahat 18",
                                "user_id": 11,
                                "pivot": {
                                    "user_id": 11,
                                    "team_id": 50,
                                    "created_at": "2021-02-15 12:04:08"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 11,
                                "name": "Center Midfield",
                                "position_id": 5,
                                "pivot": {
                                    "user_id": 11,
                                    "position_id": 5
                                }
                            }
                        ]
                    },
                    {
                        "id": 12,
                        "name": "tr 2 rerum",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 8,
                            "user_id": 12
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T11:14:18.000000Z"
            },
            {
                "id": 189,
                "name": "Famiiii 2",
                "title": null,
                "picture": "media/users/605c7a3e1a79a1616673342.jpeg",
                "last_message": {
                    "id": 1242,
                    "group_id": 189,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "fgfg",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-07-08 10:46:10",
                    "updated_at": "2021-07-08 10:46:10",
                    "msg_identification": "1625741169364",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 389,
                        "name": "Famiiii 2",
                        "profile_picture": "media/users/605c7a3e1a79a1616673342.jpeg",
                        "pivot": {
                            "group_id": 189,
                            "user_id": 389
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-06-07T15:33:08.000000Z"
            },
            {
                "id": 272,
                "name": null,
                "title": "maheen's group",
                "picture": "media/chats/groups/qpoDCJ26V3GuBbFu3vJUdk8lCiH063gYkqiVkWUR.jpg",
                "last_message": {
                    "id": 1233,
                    "group_id": 272,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "dfdfsfd",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-07-08 10:23:02",
                    "updated_at": "2021-07-08 10:23:02",
                    "msg_identification": "1625739781787",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 128,
                        "name": "baran erdogan",
                        "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
                        "pivot": {
                            "group_id": 272,
                            "user_id": 128
                        },
                        "teams": [
                            {
                                "team_id": 2,
                                "team_name": "Ajax U16",
                                "user_id": 128,
                                "pivot": {
                                    "user_id": 128,
                                    "team_id": 2,
                                    "created_at": "2020-10-28 19:47:33"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 128,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 128,
                                    "position_id": 10
                                }
                            }
                        ]
                    },
                    {
                        "id": 134,
                        "name": null,
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 272,
                            "user_id": 134
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 134,
                                "pivot": {
                                    "user_id": 134,
                                    "team_id": 5,
                                    "created_at": "2020-10-30 14:12:59"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 136,
                        "name": "erik eijgenstein",
                        "profile_picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                        "pivot": {
                            "group_id": 272,
                            "user_id": 136
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 136,
                                "pivot": {
                                    "user_id": 136,
                                    "team_id": 5,
                                    "created_at": "2020-10-31 16:03:25"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 136,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 136,
                                    "position_id": 10
                                }
                            }
                        ]
                    },
                    {
                        "id": 137,
                        "name": "wqwq wqwq",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 272,
                            "user_id": 137
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 137,
                                "pivot": {
                                    "user_id": 137,
                                    "team_id": 5,
                                    "created_at": "2020-11-01 12:29:49"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 140,
                        "name": "Christiano Ronaldo",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 272,
                            "user_id": 140
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 140,
                                "pivot": {
                                    "user_id": 140,
                                    "team_id": 5,
                                    "created_at": "2020-11-02 19:37:19"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 140,
                                "name": "Left Midfield",
                                "position_id": 6,
                                "pivot": {
                                    "user_id": 140,
                                    "position_id": 6
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T10:11:38.000000Z"
            },
            {
                "id": 33,
                "name": "Tariq Siddiqui",
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 1231,
                    "group_id": 33,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "gfg",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-07-07 21:21:34",
                    "updated_at": "2021-07-07 21:21:34",
                    "msg_identification": "1625692892262",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 178,
                        "name": "Tariq Siddiqui",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 33,
                            "user_id": 178
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-31T15:10:56.000000Z"
            },
            {
                "id": 9,
                "name": null,
                "title": "Jogo",
                "picture": "media/chats/groups/SFqplTpbA1YYkdqftTAiJ7WGmdQnuKQpOmNggOOj.jpg",
                "last_message": {
                    "id": 1175,
                    "group_id": 9,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": null,
                    "image": "media/chats/0ZkEsYgaN1qebS8vZyRnulthcCnL3q50lXNMw2J9.jpg",
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": "image",
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-06-28 08:17:36",
                    "updated_at": "2021-06-28 08:17:36",
                    "msg_identification": "1624868253592",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 155,
                        "name": "M J",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 9,
                            "user_id": 155
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 155,
                                "name": "Goal Keeper",
                                "position_id": 3,
                                "pivot": {
                                    "user_id": 155,
                                    "position_id": 3
                                }
                            }
                        ]
                    },
                    {
                        "id": 210,
                        "name": "Test Trainer",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 9,
                            "user_id": 210
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 211,
                        "name": "Trainer Name",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 9,
                            "user_id": 211
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 212,
                        "name": "Trainer Testing",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 9,
                            "user_id": 212
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 213,
                        "name": "a v",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 9,
                            "user_id": 213
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 390,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/605dd0b272be71616761010.jpeg",
                        "pivot": {
                            "group_id": 9,
                            "user_id": 390
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-05-19T11:00:18.000000Z"
            },
            {
                "id": 32,
                "name": "Umer Shaikh",
                "title": null,
                "picture": "media/users/605dd0b272be71616761010.jpeg",
                "last_message": {
                    "id": 1126,
                    "group_id": 32,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "ghh",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": "null",
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-06-10 09:36:03",
                    "updated_at": "2021-06-10 09:36:03",
                    "msg_identification": "1623317762733",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 390,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/605dd0b272be71616761010.jpeg",
                        "pivot": {
                            "group_id": 32,
                            "user_id": 390
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-29T12:20:29.000000Z"
            },
            {
                "id": 6,
                "name": "Fatima Sultana,Fami Sultana",
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 1080,
                    "group_id": 6,
                    "sender_id": 40,
                    "reply_of": 314,
                    "message": "hi",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": "null",
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-05-28 10:36:22",
                    "updated_at": "2021-05-28 10:36:22",
                    "msg_identification": "1622198181784",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 7,
                        "name": "Fatima Sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 6,
                            "user_id": 7
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 6,
                        "name": "Fami Sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 6,
                            "user_id": 6
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 6,
                                "name": "Center Midfield",
                                "position_id": 5,
                                "pivot": {
                                    "user_id": 6,
                                    "position_id": 5
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T09:59:45.000000Z"
            },
            {
                "id": 65,
                "name": "s",
                "title": "s",
                "picture": null,
                "last_message": {
                    "id": 1078,
                    "group_id": 65,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "f",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-05-26 15:13:36",
                    "updated_at": "2021-05-26 15:13:36",
                    "msg_identification": "1622042015059",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-04-19T11:27:01.000000Z"
            },
            {
                "id": 68,
                "name": "team3",
                "title": "team3",
                "picture": null,
                "last_message": {
                    "id": 1070,
                    "group_id": 68,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": null,
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": "https://media1.giphy.com/media/RPSXdpgvKh7d3FOvFo/giphy.gif?cid=09f87aa486kpeirv86cra44iqcocwy5jv769peeczj6qipyq&rid=giphy.gif&ct=g",
                    "attachment_type": "gif",
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-05-24 08:56:29",
                    "updated_at": "2021-05-24 08:56:29",
                    "msg_identification": "1621846587501",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-04-21T11:51:09.000000Z"
            },
            {
                "id": 184,
                "name": null,
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 1065,
                    "group_id": 184,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "hi",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": "null",
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-05-20 12:43:12",
                    "updated_at": "2021-05-20 12:43:12",
                    "msg_identification": "1621514591937",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-05-20T12:42:57.000000Z"
            },
            {
                "id": 5,
                "name": "Fatima Sultana",
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 984,
                    "group_id": 5,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "there",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-05-11 12:10:56",
                    "updated_at": "2021-05-11 12:10:56",
                    "msg_identification": "1620735054865",
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 7,
                        "name": "Fatima Sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 5,
                            "user_id": 7
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T09:59:23.000000Z"
            },
            {
                "id": 20,
                "name": "baran erdogan,,erik eijgenstein",
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 208,
                    "group_id": 20,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "Hey",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-03-29 11:12:51",
                    "updated_at": "2021-03-29 11:12:51",
                    "msg_identification": null,
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 128,
                        "name": "baran erdogan",
                        "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
                        "pivot": {
                            "group_id": 20,
                            "user_id": 128
                        },
                        "teams": [
                            {
                                "team_id": 2,
                                "team_name": "Ajax U16",
                                "user_id": 128,
                                "pivot": {
                                    "user_id": 128,
                                    "team_id": 2,
                                    "created_at": "2020-10-28 19:47:33"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 128,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 128,
                                    "position_id": 10
                                }
                            }
                        ]
                    },
                    {
                        "id": 131,
                        "name": null,
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 20,
                            "user_id": 131
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 136,
                        "name": "erik eijgenstein",
                        "profile_picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                        "pivot": {
                            "group_id": 20,
                            "user_id": 136
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 136,
                                "pivot": {
                                    "user_id": 136,
                                    "team_id": 5,
                                    "created_at": "2020-10-31 16:03:25"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 136,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 136,
                                    "position_id": 10
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T13:08:38.000000Z"
            },
            {
                "id": 17,
                "name": null,
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 86,
                    "group_id": 17,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "Yup",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-03-24 10:35:46",
                    "updated_at": "2021-03-24 10:35:46",
                    "msg_identification": null,
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-03-22T13:02:10.000000Z"
            },
            {
                "id": 21,
                "name": "wqwq wqwq,baran erdogan",
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 84,
                    "group_id": 21,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "hello",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-03-24 10:13:37",
                    "updated_at": "2021-03-24 10:13:37",
                    "msg_identification": null,
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 137,
                        "name": "wqwq wqwq",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 21,
                            "user_id": 137
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 137,
                                "pivot": {
                                    "user_id": 137,
                                    "team_id": 5,
                                    "created_at": "2020-11-01 12:29:49"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 128,
                        "name": "baran erdogan",
                        "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
                        "pivot": {
                            "group_id": 21,
                            "user_id": 128
                        },
                        "teams": [
                            {
                                "team_id": 2,
                                "team_name": "Ajax U16",
                                "user_id": 128,
                                "pivot": {
                                    "user_id": 128,
                                    "team_id": 2,
                                    "created_at": "2020-10-28 19:47:33"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 128,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 128,
                                    "position_id": 10
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T13:11:44.000000Z"
            },
            {
                "id": 3,
                "name": "Fami sultana",
                "title": null,
                "picture": null,
                "last_message": {
                    "id": 34,
                    "group_id": 3,
                    "sender_id": 40,
                    "reply_of": null,
                    "message": "123",
                    "image": null,
                    "file": null,
                    "file_orignal_name": null,
                    "gif_url": null,
                    "attachment_type": null,
                    "type": null,
                    "ref_message_id": null,
                    "created_at": "2021-03-22 19:45:51",
                    "updated_at": "2021-03-22 19:45:51",
                    "msg_identification": null,
                    "height": 0,
                    "width": 0,
                    "sender": {
                        "id": 40,
                        "name": "Umer Shaikh",
                        "profile_picture": "media/users/ULKlM2u7l8MLun5d0w8sLbRXL9PHebcIBS3dLIsb.jpg"
                    }
                },
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 0,
                "members": [
                    {
                        "id": 142,
                        "name": "Fami sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 3,
                            "user_id": 142
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 142,
                                "pivot": {
                                    "user_id": 142,
                                    "team_id": 5,
                                    "created_at": "2020-11-03 07:37:16"
                                }
                            }
                        ],
                        "position": []
                    }
                ],
                "total_unread_count": 7,
                "created_at": "2021-03-22T09:56:36.000000Z"
            },
            {
                "id": 256,
                "name": "T2",
                "title": null,
                "picture": "media/clubs/9aDAuyZdtksz82Gc1lQYQNibuEiaQRZUpwPRbFop.png",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-07T16:26:36.000000Z"
            },
            {
                "id": 270,
                "name": null,
                "title": "group1",
                "picture": "media/chats/groups/fVXb6Btjb8pgR6Jj2PAFRxAC0cUX01VmrDlrwcMu.jpg",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 128,
                        "name": "baran erdogan",
                        "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
                        "pivot": {
                            "group_id": 270,
                            "user_id": 128
                        },
                        "teams": [
                            {
                                "team_id": 2,
                                "team_name": "Ajax U16",
                                "user_id": 128,
                                "pivot": {
                                    "user_id": 128,
                                    "team_id": 2,
                                    "created_at": "2020-10-28 19:47:33"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 128,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 128,
                                    "position_id": 10
                                }
                            }
                        ]
                    },
                    {
                        "id": 134,
                        "name": null,
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 270,
                            "user_id": 134
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 134,
                                "pivot": {
                                    "user_id": 134,
                                    "team_id": 5,
                                    "created_at": "2020-10-30 14:12:59"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 136,
                        "name": "erik eijgenstein",
                        "profile_picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                        "pivot": {
                            "group_id": 270,
                            "user_id": 136
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 136,
                                "pivot": {
                                    "user_id": 136,
                                    "team_id": 5,
                                    "created_at": "2020-10-31 16:03:25"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 136,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 136,
                                    "position_id": 10
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T10:07:24.000000Z"
            },
            {
                "id": 259,
                "name": "yyyy",
                "title": null,
                "picture": "media/clubs/9aDAuyZdtksz82Gc1lQYQNibuEiaQRZUpwPRbFop.png",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-07T16:45:33.000000Z"
            },
            {
                "id": 239,
                "name": null,
                "title": "Geforce",
                "picture": "media/chats/groups/wuXBwDf1RkEO7nF7QFZdBtbRZBsOj8jlSVB5LTVr.png",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 4,
                        "name": "Tariq Sidd",
                        "profile_picture": "media/users/5f7b294249cca1601907010.jpeg",
                        "pivot": {
                            "group_id": 239,
                            "user_id": 4
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 4,
                                "pivot": {
                                    "user_id": 4,
                                    "team_id": 5,
                                    "created_at": null
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 4,
                                "name": "Left Back",
                                "position_id": 1,
                                "pivot": {
                                    "user_id": 4,
                                    "position_id": 1
                                }
                            }
                        ]
                    },
                    {
                        "id": 32,
                        "name": null,
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 239,
                            "user_id": 32
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-05T13:17:46.000000Z"
            },
            {
                "id": 258,
                "name": "T2",
                "title": null,
                "picture": "media/clubs/9aDAuyZdtksz82Gc1lQYQNibuEiaQRZUpwPRbFop.png",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-07T16:44:22.000000Z"
            },
            {
                "id": 241,
                "name": "Indoor team",
                "title": "Indoor team",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T16:12:58.000000Z"
            },
            {
                "id": 257,
                "name": "yyyyp",
                "title": null,
                "picture": "media/clubs/9aDAuyZdtksz82Gc1lQYQNibuEiaQRZUpwPRbFop.png",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-07T16:27:30.000000Z"
            },
            {
                "id": 249,
                "name": "teammmm testtt2updatedd Ali",
                "title": "teammmm testtt",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T17:30:07.000000Z"
            },
            {
                "id": 248,
                "name": null,
                "title": "dsd updateddd",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T17:20:14.000000Z"
            },
            {
                "id": 255,
                "name": "Team Existsssss",
                "title": "Team Exists",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-07T12:59:55.000000Z"
            },
            {
                "id": 250,
                "name": "new temmm updt",
                "title": "new temmm",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T17:40:33.000000Z"
            },
            {
                "id": 243,
                "name": "a",
                "title": "a",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T16:54:49.000000Z"
            },
            {
                "id": 244,
                "name": null,
                "title": "qq",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T16:58:50.000000Z"
            },
            {
                "id": 245,
                "name": null,
                "title": "dsd",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T17:06:24.000000Z"
            },
            {
                "id": 246,
                "name": null,
                "title": "dsd",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T17:18:31.000000Z"
            },
            {
                "id": 247,
                "name": null,
                "title": "dsd",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T17:19:33.000000Z"
            },
            {
                "id": 242,
                "name": null,
                "title": "Alpha bravo charlie",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T16:29:36.000000Z"
            },
            {
                "id": 279,
                "name": null,
                "title": "fixes group",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 650,
                        "name": "Saad Bhai",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 279,
                            "user_id": 650
                        },
                        "teams": [
                            {
                                "team_id": 112,
                                "team_name": "teammmm testtt2updatedd Ali",
                                "user_id": 650,
                                "pivot": {
                                    "user_id": 650,
                                    "team_id": 112,
                                    "created_at": "2021-07-06 09:40:39"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 650,
                                "name": "Right midfield",
                                "position_id": 8,
                                "pivot": {
                                    "user_id": 650,
                                    "position_id": 8
                                }
                            }
                        ]
                    },
                    {
                        "id": 649,
                        "name": "Dilawer Abbas",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 279,
                            "user_id": 649
                        },
                        "teams": [
                            {
                                "team_id": 112,
                                "team_name": "teammmm testtt2updatedd Ali",
                                "user_id": 649,
                                "pivot": {
                                    "user_id": 649,
                                    "team_id": 112,
                                    "created_at": "2021-07-06 09:39:32"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 649,
                                "name": "Left Back",
                                "position_id": 1,
                                "pivot": {
                                    "user_id": 649,
                                    "position_id": 1
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T18:34:57.000000Z"
            },
            {
                "id": 273,
                "name": null,
                "title": "the na",
                "picture": "media/chats/groups/ptFjWJwwzluV87qTWFvIepkz7ocNCKpUaC8LlvLL.png",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 6,
                        "name": "Fami Sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 273,
                            "user_id": 6
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 6,
                                "name": "Center Midfield",
                                "position_id": 5,
                                "pivot": {
                                    "user_id": 6,
                                    "position_id": 5
                                }
                            }
                        ]
                    },
                    {
                        "id": 3,
                        "name": "Hasnain Ali",
                        "profile_picture": "media/users/609bdad748e321620826839.jpeg",
                        "pivot": {
                            "group_id": 273,
                            "user_id": 3
                        },
                        "teams": [
                            {
                                "team_id": 3,
                                "team_name": "ManUtd U18",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 3,
                                    "created_at": "2020-07-16 15:51:01"
                                }
                            },
                            {
                                "team_id": 4,
                                "team_name": "test team",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 4,
                                    "created_at": null
                                }
                            },
                            {
                                "team_id": 4,
                                "team_name": "test team",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 4,
                                    "created_at": null
                                }
                            },
                            {
                                "team_id": 3,
                                "team_name": "ManUtd U18",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 3,
                                    "created_at": null
                                }
                            },
                            {
                                "team_id": 4,
                                "team_name": "test team",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 4,
                                    "created_at": null
                                }
                            },
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 5,
                                    "created_at": null
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 3,
                                "name": "Right Back",
                                "position_id": 2,
                                "pivot": {
                                    "user_id": 3,
                                    "position_id": 2
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-09T09:39:50.000000Z"
            },
            {
                "id": 285,
                "name": null,
                "title": "qwqwq",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 167,
                        "name": "Lionel Messi",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 285,
                            "user_id": 167
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 166,
                        "name": "First Name Last Name",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 285,
                            "user_id": 166
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 164,
                        "name": "argentina player",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 285,
                            "user_id": 164
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 175,
                        "name": "Judith de Bruin",
                        "profile_picture": "media/users/5fe0ac60a992b1608559712.jpeg",
                        "pivot": {
                            "group_id": 285,
                            "user_id": 175
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 175,
                                "pivot": {
                                    "user_id": 175,
                                    "team_id": 5,
                                    "created_at": "2020-12-21 13:08:39"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 175,
                                "name": "Left Wing",
                                "position_id": 7,
                                "pivot": {
                                    "user_id": 175,
                                    "position_id": 7
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T20:43:01.000000Z"
            },
            {
                "id": 292,
                "name": null,
                "title": "wewrsfdgrdt",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-09T07:47:55.000000Z"
            },
            {
                "id": 291,
                "name": null,
                "title": "group111",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-09T07:13:19.000000Z"
            },
            {
                "id": 290,
                "name": null,
                "title": "saqsaq",
                "picture": "media/users/5f99cb1c40f871603914524.jpeg",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 128,
                        "name": "baran erdogan",
                        "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
                        "pivot": {
                            "group_id": 290,
                            "user_id": 128
                        },
                        "teams": [
                            {
                                "team_id": 2,
                                "team_name": "Ajax U16",
                                "user_id": 128,
                                "pivot": {
                                    "user_id": 128,
                                    "team_id": 2,
                                    "created_at": "2020-10-28 19:47:33"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 128,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 128,
                                    "position_id": 10
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T22:07:57.000000Z"
            },
            {
                "id": 289,
                "name": null,
                "title": "wewewew",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 136,
                        "name": "erik eijgenstein",
                        "profile_picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                        "pivot": {
                            "group_id": 289,
                            "user_id": 136
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 136,
                                "pivot": {
                                    "user_id": 136,
                                    "team_id": 5,
                                    "created_at": "2020-10-31 16:03:25"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 136,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 136,
                                    "position_id": 10
                                }
                            }
                        ]
                    },
                    {
                        "id": 143,
                        "name": "Bram Vijgen",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 289,
                            "user_id": 143
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 143,
                                "pivot": {
                                    "user_id": 143,
                                    "team_id": 5,
                                    "created_at": "2020-11-10 19:02:41"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 143,
                                "name": "Goal Keeper",
                                "position_id": 3,
                                "pivot": {
                                    "user_id": 143,
                                    "position_id": 3
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T22:04:25.000000Z"
            },
            {
                "id": 288,
                "name": null,
                "title": "qswqwq",
                "picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 136,
                        "name": "erik eijgenstein",
                        "profile_picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                        "pivot": {
                            "group_id": 288,
                            "user_id": 136
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 136,
                                "pivot": {
                                    "user_id": 136,
                                    "team_id": 5,
                                    "created_at": "2020-10-31 16:03:25"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 136,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 136,
                                    "position_id": 10
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T20:54:40.000000Z"
            },
            {
                "id": 287,
                "name": null,
                "title": "fdfdfjjyyy",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 136,
                        "name": "erik eijgenstein",
                        "profile_picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                        "pivot": {
                            "group_id": 287,
                            "user_id": 136
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 136,
                                "pivot": {
                                    "user_id": 136,
                                    "team_id": 5,
                                    "created_at": "2020-10-31 16:03:25"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 136,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 136,
                                    "position_id": 10
                                }
                            }
                        ]
                    },
                    {
                        "id": 156,
                        "name": "Hasnain Ali",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 287,
                            "user_id": 156
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 177,
                        "name": "Umer Sheikh",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 287,
                            "user_id": 177
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 196,
                        "name": "Khurram Munir",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 287,
                            "user_id": 196
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 196,
                                "pivot": {
                                    "user_id": 196,
                                    "team_id": 5,
                                    "created_at": "2021-01-08 10:33:36"
                                }
                            }
                        ],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T20:51:51.000000Z"
            },
            {
                "id": 286,
                "name": null,
                "title": "finalll",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 653,
                        "name": "Kazim Raza",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 286,
                            "user_id": 653
                        },
                        "teams": [
                            {
                                "team_id": 21,
                                "team_name": "Indoor team",
                                "user_id": 653,
                                "pivot": {
                                    "user_id": 653,
                                    "team_id": 21,
                                    "created_at": "2021-07-07 13:02:34"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 653,
                                "name": "Right midfield",
                                "position_id": 8,
                                "pivot": {
                                    "user_id": 653,
                                    "position_id": 8
                                }
                            }
                        ]
                    },
                    {
                        "id": 650,
                        "name": "Saad Bhai",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 286,
                            "user_id": 650
                        },
                        "teams": [
                            {
                                "team_id": 112,
                                "team_name": "teammmm testtt2updatedd Ali",
                                "user_id": 650,
                                "pivot": {
                                    "user_id": 650,
                                    "team_id": 112,
                                    "created_at": "2021-07-06 09:40:39"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 650,
                                "name": "Right midfield",
                                "position_id": 8,
                                "pivot": {
                                    "user_id": 650,
                                    "position_id": 8
                                }
                            }
                        ]
                    },
                    {
                        "id": 649,
                        "name": "Dilawer Abbas",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 286,
                            "user_id": 649
                        },
                        "teams": [
                            {
                                "team_id": 112,
                                "team_name": "teammmm testtt2updatedd Ali",
                                "user_id": 649,
                                "pivot": {
                                    "user_id": 649,
                                    "team_id": 112,
                                    "created_at": "2021-07-06 09:39:32"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 649,
                                "name": "Left Back",
                                "position_id": 1,
                                "pivot": {
                                    "user_id": 649,
                                    "position_id": 1
                                }
                            }
                        ]
                    },
                    {
                        "id": 648,
                        "name": "Mehdi Rizvi",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 286,
                            "user_id": 648
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 648,
                                "pivot": {
                                    "user_id": 648,
                                    "team_id": 5,
                                    "created_at": "2021-07-06 09:36:43"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 648,
                                "name": "Center Midfield",
                                "position_id": 5,
                                "pivot": {
                                    "user_id": 648,
                                    "position_id": 5
                                }
                            }
                        ]
                    },
                    {
                        "id": 567,
                        "name": "ai a",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 286,
                            "user_id": 567
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 567,
                                "name": "Left Wing",
                                "position_id": 7,
                                "pivot": {
                                    "user_id": 567,
                                    "position_id": 7
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T20:49:27.000000Z"
            },
            {
                "id": 284,
                "name": null,
                "title": "sasasasderer",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 160,
                        "name": "player2 abc",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 284,
                            "user_id": 160
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 159,
                        "name": "first name last name",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 284,
                            "user_id": 159
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 156,
                        "name": "Hasnain Ali",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 284,
                            "user_id": 156
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T20:41:11.000000Z"
            },
            {
                "id": 274,
                "name": null,
                "title": "group1",
                "picture": "media/chats/groups/9qkzCbmawnsCm0b8OJ7OLp06ON1JOjIEE873ktll.jpg",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 154,
                        "name": "Michael Jackson",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 274,
                            "user_id": 154
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 154,
                                "pivot": {
                                    "user_id": 154,
                                    "team_id": 5,
                                    "created_at": "2020-12-14 14:55:07"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 155,
                        "name": "M J",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 274,
                            "user_id": 155
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 155,
                                "name": "Goal Keeper",
                                "position_id": 3,
                                "pivot": {
                                    "user_id": 155,
                                    "position_id": 3
                                }
                            }
                        ]
                    },
                    {
                        "id": 156,
                        "name": "Hasnain Ali",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 274,
                            "user_id": 156
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T15:55:31.000000Z"
            },
            {
                "id": 283,
                "name": null,
                "title": "asasasasasasas",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 152,
                        "name": "alvaro montero",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 283,
                            "user_id": 152
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 152,
                                "pivot": {
                                    "user_id": 152,
                                    "team_id": 5,
                                    "created_at": "2020-12-03 14:33:52"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 150,
                        "name": null,
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 283,
                            "user_id": 150
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 150,
                                "pivot": {
                                    "user_id": 150,
                                    "team_id": 5,
                                    "created_at": "2020-11-30 11:03:25"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 143,
                        "name": "Bram Vijgen",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 283,
                            "user_id": 143
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 143,
                                "pivot": {
                                    "user_id": 143,
                                    "team_id": 5,
                                    "created_at": "2020-11-10 19:02:41"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 143,
                                "name": "Goal Keeper",
                                "position_id": 3,
                                "pivot": {
                                    "user_id": 143,
                                    "position_id": 3
                                }
                            }
                        ]
                    },
                    {
                        "id": 155,
                        "name": "M J",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 283,
                            "user_id": 155
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 155,
                                "name": "Goal Keeper",
                                "position_id": 3,
                                "pivot": {
                                    "user_id": 155,
                                    "position_id": 3
                                }
                            }
                        ]
                    },
                    {
                        "id": 156,
                        "name": "Hasnain Ali",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 283,
                            "user_id": 156
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T20:35:37.000000Z"
            },
            {
                "id": 282,
                "name": null,
                "title": "v3 test",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 164,
                        "name": "argentina player",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 282,
                            "user_id": 164
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 167,
                        "name": "Lionel Messi",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 282,
                            "user_id": 167
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 166,
                        "name": "First Name Last Name",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 282,
                            "user_id": 166
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 163,
                        "name": "abv def",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 282,
                            "user_id": 163
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T20:33:14.000000Z"
            },
            {
                "id": 281,
                "name": "wqwq wqwq,,alvaro montero",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 137,
                        "name": "wqwq wqwq",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 281,
                            "user_id": 137
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 137,
                                "pivot": {
                                    "user_id": 137,
                                    "team_id": 5,
                                    "created_at": "2020-11-01 12:29:49"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 150,
                        "name": null,
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 281,
                            "user_id": 150
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 150,
                                "pivot": {
                                    "user_id": 150,
                                    "team_id": 5,
                                    "created_at": "2020-11-30 11:03:25"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 152,
                        "name": "alvaro montero",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 281,
                            "user_id": 152
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 152,
                                "pivot": {
                                    "user_id": 152,
                                    "team_id": 5,
                                    "created_at": "2020-12-03 14:33:52"
                                }
                            }
                        ],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T19:06:08.000000Z"
            },
            {
                "id": 280,
                "name": null,
                "title": "chat here",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 651,
                        "name": "Faraz Ali",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 280,
                            "user_id": 651
                        },
                        "teams": [
                            {
                                "team_id": 21,
                                "team_name": "Indoor team",
                                "user_id": 651,
                                "pivot": {
                                    "user_id": 651,
                                    "team_id": 21,
                                    "created_at": "2021-07-06 09:44:21"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 651,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 651,
                                    "position_id": 10
                                }
                            }
                        ]
                    },
                    {
                        "id": 649,
                        "name": "Dilawer Abbas",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 280,
                            "user_id": 649
                        },
                        "teams": [
                            {
                                "team_id": 112,
                                "team_name": "teammmm testtt2updatedd Ali",
                                "user_id": 649,
                                "pivot": {
                                    "user_id": 649,
                                    "team_id": 112,
                                    "created_at": "2021-07-06 09:39:32"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 649,
                                "name": "Left Back",
                                "position_id": 1,
                                "pivot": {
                                    "user_id": 649,
                                    "position_id": 1
                                }
                            }
                        ]
                    },
                    {
                        "id": 569,
                        "name": "asa asa",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 280,
                            "user_id": 569
                        },
                        "teams": [
                            {
                                "team_id": 21,
                                "team_name": "Indoor team",
                                "user_id": 569,
                                "pivot": {
                                    "user_id": 569,
                                    "team_id": 21,
                                    "created_at": "2021-06-22 14:41:26"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 569,
                                "name": "Left Wing",
                                "position_id": 7,
                                "pivot": {
                                    "user_id": 569,
                                    "position_id": 7
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T18:57:40.000000Z"
            },
            {
                "id": 232,
                "name": "11 m",
                "title": "11 m",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-02T13:54:48.000000Z"
            },
            {
                "id": 278,
                "name": null,
                "title": "Mehdi's group",
                "picture": "media/chats/groups/hjjrWAjnyIh1IVItIx8QlWYwuyh2dt5TuPeQLqav.jpg",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 653,
                        "name": "Kazim Raza",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 278,
                            "user_id": 653
                        },
                        "teams": [
                            {
                                "team_id": 21,
                                "team_name": "Indoor team",
                                "user_id": 653,
                                "pivot": {
                                    "user_id": 653,
                                    "team_id": 21,
                                    "created_at": "2021-07-07 13:02:34"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 653,
                                "name": "Right midfield",
                                "position_id": 8,
                                "pivot": {
                                    "user_id": 653,
                                    "position_id": 8
                                }
                            }
                        ]
                    },
                    {
                        "id": 651,
                        "name": "Faraz Ali",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 278,
                            "user_id": 651
                        },
                        "teams": [
                            {
                                "team_id": 21,
                                "team_name": "Indoor team",
                                "user_id": 651,
                                "pivot": {
                                    "user_id": 651,
                                    "team_id": 21,
                                    "created_at": "2021-07-06 09:44:21"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 651,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 651,
                                    "position_id": 10
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T18:14:55.000000Z"
            },
            {
                "id": 276,
                "name": null,
                "title": "maheen finalll group",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 137,
                        "name": "wqwq wqwq",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 276,
                            "user_id": 137
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 137,
                                "pivot": {
                                    "user_id": 137,
                                    "team_id": 5,
                                    "created_at": "2020-11-01 12:29:49"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 141,
                        "name": "Fahad Paapi",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 276,
                            "user_id": 141
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 141,
                                "pivot": {
                                    "user_id": 141,
                                    "team_id": 5,
                                    "created_at": "2020-11-02 20:18:57"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 141,
                                "name": "Center Back",
                                "position_id": 4,
                                "pivot": {
                                    "user_id": 141,
                                    "position_id": 4
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T16:42:47.000000Z"
            },
            {
                "id": 275,
                "name": null,
                "title": "my groupp",
                "picture": "media/chats/groups/5ed2bCr5ovFIERR1JPesIzFphlVtwOpvb5JkwKFE.jpg",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 142,
                        "name": "Fami sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 275,
                            "user_id": 142
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 142,
                                "pivot": {
                                    "user_id": 142,
                                    "team_id": 5,
                                    "created_at": "2020-11-03 07:37:16"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 140,
                        "name": "Christiano Ronaldo",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 275,
                            "user_id": 140
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 140,
                                "pivot": {
                                    "user_id": 140,
                                    "team_id": 5,
                                    "created_at": "2020-11-02 19:37:19"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 140,
                                "name": "Left Midfield",
                                "position_id": 6,
                                "pivot": {
                                    "user_id": 140,
                                    "position_id": 6
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-08T16:12:11.000000Z"
            },
            {
                "id": 236,
                "name": null,
                "title": "TEST FOR DEV FAHAD",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-05T11:50:38.000000Z"
            },
            {
                "id": 223,
                "name": "Team1",
                "title": "Team1",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-01T15:29:31.000000Z"
            },
            {
                "id": 231,
                "name": "teamname OO",
                "title": "teamname OO",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-02T13:50:49.000000Z"
            },
            {
                "id": 22,
                "name": ",baran erdogan,Michael Jackson",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 131,
                        "name": null,
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 22,
                            "user_id": 131
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 128,
                        "name": "baran erdogan",
                        "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
                        "pivot": {
                            "group_id": 22,
                            "user_id": 128
                        },
                        "teams": [
                            {
                                "team_id": 2,
                                "team_name": "Ajax U16",
                                "user_id": 128,
                                "pivot": {
                                    "user_id": 128,
                                    "team_id": 2,
                                    "created_at": "2020-10-28 19:47:33"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 128,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 128,
                                    "position_id": 10
                                }
                            }
                        ]
                    },
                    {
                        "id": 154,
                        "name": "Michael Jackson",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 22,
                            "user_id": 154
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 154,
                                "pivot": {
                                    "user_id": 154,
                                    "team_id": 5,
                                    "created_at": "2020-12-14 14:55:07"
                                }
                            }
                        ],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T13:13:38.000000Z"
            },
            {
                "id": 195,
                "name": "ai a",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 567,
                        "name": "ai a",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 195,
                            "user_id": 567
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 567,
                                "name": "Left Wing",
                                "position_id": 7,
                                "pivot": {
                                    "user_id": 567,
                                    "position_id": 7
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-06-21T19:35:48.000000Z"
            },
            {
                "id": 188,
                "name": null,
                "title": "Team1",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 169,
                        "name": "Trainer Abc",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 188,
                            "user_id": 169
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 417,
                        "name": "23 32",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 188,
                            "user_id": 417
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 485,
                        "name": "ew ewew",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 188,
                            "user_id": 485
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-06-07T14:48:48.000000Z"
            },
            {
                "id": 187,
                "name": null,
                "title": "testtt",
                "picture": "media/chats/groups/lRwqwVjFj6c0u8sO4pk51cNIO0zZpmYt15etZf4W.jpg",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 142,
                        "name": "Fami sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 187,
                            "user_id": 142
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 142,
                                "pivot": {
                                    "user_id": 142,
                                    "team_id": 5,
                                    "created_at": "2020-11-03 07:37:16"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 178,
                        "name": "Tariq Siddiqui",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 187,
                            "user_id": 178
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-05-28T11:00:25.000000Z"
            },
            {
                "id": 186,
                "name": null,
                "title": "Check",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 192,
                        "name": "h w",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 186,
                            "user_id": 192
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 193,
                        "name": "h w",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 186,
                            "user_id": 193
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 201,
                        "name": "a b",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 186,
                            "user_id": 201
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 202,
                        "name": "a b",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 186,
                            "user_id": 202
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 203,
                        "name": "1 Player ab",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 186,
                            "user_id": 203
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 231,
                        "name": "corrupti id",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 186,
                            "user_id": 231
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 430,
                        "name": "4 4",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 186,
                            "user_id": 430
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 319,
                        "name": "Player Name",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 186,
                            "user_id": 319
                        },
                        "teams": [
                            {
                                "team_id": 36,
                                "team_name": "Street 12",
                                "user_id": 319,
                                "pivot": {
                                    "user_id": 319,
                                    "team_id": 36,
                                    "created_at": "2021-01-15 20:08:57"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 439,
                        "name": "r r",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 186,
                            "user_id": 439
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-05-20T14:13:12.000000Z"
            },
            {
                "id": 185,
                "name": "sds ds,wew ewew,rere erer,dfd dfdf,fd fd,dwew ewew,fdf fdf",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 418,
                        "name": "sds ds",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 185,
                            "user_id": 418
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 419,
                        "name": "wew ewew",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 185,
                            "user_id": 419
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 422,
                        "name": "rere erer",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 185,
                            "user_id": 422
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 423,
                        "name": "dfd dfdf",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 185,
                            "user_id": 423
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 425,
                        "name": "fd fd",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 185,
                            "user_id": 425
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 424,
                        "name": "dwew ewew",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 185,
                            "user_id": 424
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 426,
                        "name": "fdf fdf",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 185,
                            "user_id": 426
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-05-20T13:34:31.000000Z"
            },
            {
                "id": 182,
                "name": null,
                "title": "new game",
                "picture": "media/chats/groups/n84ET7oLB9KfcN4uZWHn2I6p2wFnsyxZlhRZaPQi.jpg",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 156,
                        "name": "Hasnain Ali",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 182,
                            "user_id": 156
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 178,
                        "name": "Tariq Siddiqui",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 182,
                            "user_id": 178
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-05-19T09:16:27.000000Z"
            },
            {
                "id": 176,
                "name": null,
                "title": "lets play",
                "picture": "media/chats/groups/Rr4xGcqgSKz2alv8OeFxnhKLQfF2eTpBh6hH0O8G.jpg",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 3,
                        "name": "Hasnain Ali",
                        "profile_picture": "media/users/609bdad748e321620826839.jpeg",
                        "pivot": {
                            "group_id": 176,
                            "user_id": 3
                        },
                        "teams": [
                            {
                                "team_id": 3,
                                "team_name": "ManUtd U18",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 3,
                                    "created_at": "2020-07-16 15:51:01"
                                }
                            },
                            {
                                "team_id": 4,
                                "team_name": "test team",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 4,
                                    "created_at": null
                                }
                            },
                            {
                                "team_id": 4,
                                "team_name": "test team",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 4,
                                    "created_at": null
                                }
                            },
                            {
                                "team_id": 3,
                                "team_name": "ManUtd U18",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 3,
                                    "created_at": null
                                }
                            },
                            {
                                "team_id": 4,
                                "team_name": "test team",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 4,
                                    "created_at": null
                                }
                            },
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 3,
                                "pivot": {
                                    "user_id": 3,
                                    "team_id": 5,
                                    "created_at": null
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 3,
                                "name": "Right Back",
                                "position_id": 2,
                                "pivot": {
                                    "user_id": 3,
                                    "position_id": 2
                                }
                            }
                        ]
                    },
                    {
                        "id": 4,
                        "name": "Tariq Sidd",
                        "profile_picture": "media/users/5f7b294249cca1601907010.jpeg",
                        "pivot": {
                            "group_id": 176,
                            "user_id": 4
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 4,
                                "pivot": {
                                    "user_id": 4,
                                    "team_id": 5,
                                    "created_at": null
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 4,
                                "name": "Left Back",
                                "position_id": 1,
                                "pivot": {
                                    "user_id": 4,
                                    "position_id": 1
                                }
                            }
                        ]
                    },
                    {
                        "id": 5,
                        "name": "Alex Ferguson",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 176,
                            "user_id": 5
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-05-18T16:02:07.000000Z"
            },
            {
                "id": 80,
                "name": "wew",
                "title": "wew",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-05-05T10:15:22.000000Z"
            },
            {
                "id": 19,
                "name": "abv def",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 163,
                        "name": "abv def",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 19,
                            "user_id": 163
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T13:02:55.000000Z"
            },
            {
                "id": 197,
                "name": "teamchat",
                "title": "teamchat",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-22T10:38:02.000000Z"
            },
            {
                "id": 18,
                "name": "first name last name",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 159,
                        "name": "first name last name",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 18,
                            "user_id": 159
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T13:02:30.000000Z"
            },
            {
                "id": 16,
                "name": "erik eijgenstein",
                "title": null,
                "picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 136,
                        "name": "erik eijgenstein",
                        "profile_picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
                        "pivot": {
                            "group_id": 16,
                            "user_id": 136
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 136,
                                "pivot": {
                                    "user_id": 136,
                                    "team_id": 5,
                                    "created_at": "2020-10-31 16:03:25"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 136,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 136,
                                    "position_id": 10
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T13:02:05.000000Z"
            },
            {
                "id": 15,
                "name": "Fahad Paapi",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 141,
                        "name": "Fahad Paapi",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 15,
                            "user_id": 141
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 141,
                                "pivot": {
                                    "user_id": 141,
                                    "team_id": 5,
                                    "created_at": "2020-11-02 20:18:57"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 141,
                                "name": "Center Back",
                                "position_id": 4,
                                "pivot": {
                                    "user_id": 141,
                                    "position_id": 4
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T13:01:40.000000Z"
            },
            {
                "id": 14,
                "name": "Kick Dwinger",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 180,
                        "name": "Kick Dwinger",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 14,
                            "user_id": 180
                        },
                        "teams": [
                            {
                                "team_id": 16,
                                "team_name": "teamname OO",
                                "user_id": 180,
                                "pivot": {
                                    "user_id": 180,
                                    "team_id": 16,
                                    "created_at": "2020-12-23 09:17:38"
                                }
                            }
                        ],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T13:00:52.000000Z"
            },
            {
                "id": 12,
                "name": "",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 134,
                        "name": null,
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 12,
                            "user_id": 134
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 134,
                                "pivot": {
                                    "user_id": 134,
                                    "team_id": 5,
                                    "created_at": "2020-10-30 14:12:59"
                                }
                            }
                        ],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T11:32:20.000000Z"
            },
            {
                "id": 11,
                "name": "baran erdogan,",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 128,
                        "name": "baran erdogan",
                        "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
                        "pivot": {
                            "group_id": 11,
                            "user_id": 128
                        },
                        "teams": [
                            {
                                "team_id": 2,
                                "team_name": "Ajax U16",
                                "user_id": 128,
                                "pivot": {
                                    "user_id": 128,
                                    "team_id": 2,
                                    "created_at": "2020-10-28 19:47:33"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 128,
                                "name": "Striker ",
                                "position_id": 10,
                                "pivot": {
                                    "user_id": 128,
                                    "position_id": 10
                                }
                            }
                        ]
                    },
                    {
                        "id": 131,
                        "name": null,
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 11,
                            "user_id": 131
                        },
                        "teams": [],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T11:31:26.000000Z"
            },
            {
                "id": 10,
                "name": "Argentina ABC",
                "title": null,
                "picture": "media/clubs/9aDAuyZdtksz82Gc1lQYQNibuEiaQRZUpwPRbFop.png",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 168,
                        "name": "Diago Maradona",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 10,
                            "user_id": 168
                        },
                        "teams": [
                            {
                                "team_id": 7,
                                "team_name": "Argentina ABC",
                                "user_id": 168,
                                "pivot": {
                                    "user_id": 168,
                                    "team_id": 7,
                                    "created_at": "2020-12-18 10:29:31"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 56,
                        "name": "david dwinger",
                        "profile_picture": "media/users/5f9bcfdf102801604046815.jpeg",
                        "pivot": {
                            "group_id": 10,
                            "user_id": 56
                        },
                        "teams": [
                            {
                                "team_id": 7,
                                "team_name": "Argentina ABC",
                                "user_id": 56,
                                "pivot": {
                                    "user_id": 56,
                                    "team_id": 7,
                                    "created_at": "2021-01-08 12:36:34"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 56,
                                "name": "Right Back",
                                "position_id": 2,
                                "pivot": {
                                    "user_id": 56,
                                    "position_id": 2
                                }
                            }
                        ]
                    },
                    {
                        "id": 365,
                        "name": "ABC a",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 10,
                            "user_id": 365
                        },
                        "teams": [
                            {
                                "team_id": 7,
                                "team_name": "Argentina ABC",
                                "user_id": 365,
                                "pivot": {
                                    "user_id": 365,
                                    "team_id": 7,
                                    "created_at": "2021-03-04 17:58:04"
                                }
                            }
                        ],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T11:31:20.000000Z"
            },
            {
                "id": 7,
                "name": "Fami Sultana,Fatima Sultana,abdul Haseeb",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 6,
                        "name": "Fami Sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 7,
                            "user_id": 6
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 6,
                                "name": "Center Midfield",
                                "position_id": 5,
                                "pivot": {
                                    "user_id": 6,
                                    "position_id": 5
                                }
                            }
                        ]
                    },
                    {
                        "id": 7,
                        "name": "Fatima Sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 7,
                            "user_id": 7
                        },
                        "teams": [],
                        "position": []
                    },
                    {
                        "id": 9,
                        "name": "abdul Haseeb",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 7,
                            "user_id": 9
                        },
                        "teams": [],
                        "position": [
                            {
                                "user_id": 9,
                                "name": "Right Back",
                                "position_id": 2,
                                "pivot": {
                                    "user_id": 9,
                                    "position_id": 2
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T10:29:31.000000Z"
            },
            {
                "id": 196,
                "name": "newTeams 123",
                "title": "newTeams",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-22T10:36:55.000000Z"
            },
            {
                "id": 198,
                "name": "T1",
                "title": null,
                "picture": "media/clubs/9aDAuyZdtksz82Gc1lQYQNibuEiaQRZUpwPRbFop.png",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-22T12:46:22.000000Z"
            },
            {
                "id": 230,
                "name": "koko",
                "title": "koko",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-02T12:57:25.000000Z"
            },
            {
                "id": 221,
                "name": "Hasnain Team A",
                "title": "Hasnain Team",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-01T10:02:51.000000Z"
            },
            {
                "id": 229,
                "name": "mj 22",
                "title": "mj 123",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-02T11:51:57.000000Z"
            },
            {
                "id": 228,
                "name": "MJ 123",
                "title": "MJ 123",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-01T16:13:24.000000Z"
            },
            {
                "id": 227,
                "name": "salam",
                "title": "salam",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-01T16:12:29.000000Z"
            },
            {
                "id": 226,
                "name": "d s",
                "title": "d",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-01T15:41:51.000000Z"
            },
            {
                "id": 225,
                "name": "Hasnain Team 123 556",
                "title": "Hasnain Team",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-01T15:31:49.000000Z"
            },
            {
                "id": 224,
                "name": "jj 1",
                "title": "Argentina",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-01T15:31:39.000000Z"
            },
            {
                "id": 4,
                "name": "Fami sultana,Bram Vijgen",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 142,
                        "name": "Fami sultana",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 4,
                            "user_id": 142
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 142,
                                "pivot": {
                                    "user_id": 142,
                                    "team_id": 5,
                                    "created_at": "2020-11-03 07:37:16"
                                }
                            }
                        ],
                        "position": []
                    },
                    {
                        "id": 143,
                        "name": "Bram Vijgen",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 4,
                            "user_id": 143
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 143,
                                "pivot": {
                                    "user_id": 143,
                                    "team_id": 5,
                                    "created_at": "2020-11-10 19:02:41"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 143,
                                "name": "Goal Keeper",
                                "position_id": 3,
                                "pivot": {
                                    "user_id": 143,
                                    "position_id": 3
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-03-22T09:58:08.000000Z"
            },
            {
                "id": 222,
                "name": "sa la",
                "title": "sa",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-07-01T15:12:42.000000Z"
            },
            {
                "id": 220,
                "name": "ABC Tamaa",
                "title": "ABC Tamaa",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-30T14:28:05.000000Z"
            },
            {
                "id": 199,
                "name": "my team",
                "title": "my team",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-22T12:49:23.000000Z"
            },
            {
                "id": 219,
                "name": "Xyz",
                "title": "ABC Tam",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-30T14:27:41.000000Z"
            },
            {
                "id": 217,
                "name": "Creeed",
                "title": "Creeed",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-30T14:24:30.000000Z"
            },
            {
                "id": 216,
                "name": "Closed To V3",
                "title": "Closed To V3",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-30T14:23:08.000000Z"
            },
            {
                "id": 215,
                "name": "Team V3",
                "title": "Team V3",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-30T14:15:26.000000Z"
            },
            {
                "id": 211,
                "name": "asa asa",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 569,
                        "name": "asa asa",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 211,
                            "user_id": 569
                        },
                        "teams": [
                            {
                                "team_id": 21,
                                "team_name": "Indoor team",
                                "user_id": 569,
                                "pivot": {
                                    "user_id": 569,
                                    "team_id": 21,
                                    "created_at": "2021-06-22 14:41:26"
                                }
                            }
                        ],
                        "position": [
                            {
                                "user_id": 569,
                                "name": "Left Wing",
                                "position_id": 7,
                                "pivot": {
                                    "user_id": 569,
                                    "position_id": 7
                                }
                            }
                        ]
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-06-23T14:28:24.000000Z"
            },
            {
                "id": 210,
                "name": "alvaro montero",
                "title": null,
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 152,
                        "name": "alvaro montero",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 210,
                            "user_id": 152
                        },
                        "teams": [
                            {
                                "team_id": 5,
                                "team_name": "consequatur",
                                "user_id": 152,
                                "pivot": {
                                    "user_id": 152,
                                    "team_id": 5,
                                    "created_at": "2020-12-03 14:33:52"
                                }
                            }
                        ],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-06-23T14:26:53.000000Z"
            },
            {
                "id": 201,
                "name": "the",
                "title": "fdhgduywgquer",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-22T12:56:03.000000Z"
            },
            {
                "id": 200,
                "name": "sdfs",
                "title": "sdfs",
                "picture": null,
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [],
                "total_unread_count": 0,
                "created_at": "2021-06-22T12:54:55.000000Z"
            },
            {
                "id": 293,
                "name": "U13",
                "title": "the n",
                "picture": "media/clubs/qAYP468JS8B7zAX9iQGIDJhmUXl37yHBwR8idSra.png",
                "last_message": {},
                "created_by": "Umer Shaikh",
                "is_online": 0,
                "is_read": 1,
                "members": [
                    {
                        "id": 236,
                        "name": "Test Player 1",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 293,
                            "user_id": 236
                        },
                        "teams": [
                            {
                                "team_id": 13,
                                "team_name": "U13",
                                "user_id": 236,
                                "pivot": {
                                    "user_id": 236,
                                    "team_id": 13,
                                    "created_at": "2021-01-14 12:35:33"
                                }
                            }
                        ],
                        "position": []
                    }
                ],
                "total_unread_count": 0,
                "created_at": "2021-07-09T09:40:34.000000Z"
            }
        ]
    }
     *
     * @return JsonResponse
     */
    /*public function contacts(Request $request){
        $groups = ChatGroup::with(['members'=>function($q){
            $q->selectRaw("users.id, CONCAT(users.first_name,' ',users.last_name) as name, users.profile_picture")
                ->with(['teams' => function($team){
                    $team->select('team_id','team_name','user_id');
                },'position' => function($position){
                    $position->select('user_id','name','position_id');
                }]);
        },'last_message.sender'=>function($q){
            $q->selectRaw("users.id, CONCAT(first_name,' ',last_name) as name, profile_picture");
        }])->whereHas('members',function ($member){
            $member->where('users.id',auth()->id());
        })->with('team')->get()->sortByDesc(function ($f){
            return @$f->last_message->id;
        })->values();
        $data = ChatGroupResource::collection($groups);
        if(!$data){
            return Helper::apiErrorResponse(false, 'no contacts found',new \stdClass());
        }
        return Helper::apiSuccessResponse(true, 'contacts',$data);
    }*/
    /**
     * Get Players
     *
     * @response{
    "Response": true,
    "StatusCode": 200,
    "Message": "Records  found",
    "Result": [
    {
    "id": 128,
    "player_name": "baran erdogan",
    "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
    "age": null,
    "gender": null,
    "position": [
    "Striker "
    ],
    "teams": [
    {
    "id": 2,
    "team_name": "Ajax U16",
    "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
    "pivot": {
    "user_id": 128,
    "team_id": 2,
    "created_at": "2020-10-28 19:47:33"
    }
    }
    ]
    }
    ]
    }
     *
     * @return JsonResponse
     */
    /*public function club_players(){
        $clubs = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->get()->pluck('club_id');
        $players = User::role('player')
            ->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture', 'users.age', 'users.gender')
            ->with([
                'teams' => function ($q) {
                    $q->select('teams.id', 'teams.team_name', 'teams.image');
                },
            ])
            ->whereHas('clubs_players', function ($q) use ($clubs) {
                $q->whereIn('club_id', $clubs);
            })
            ->with([
                'player' => function ($q1) {
                    $q1->select('players.id', 'players.user_id', 'players.position_id');
                    $q1->with('position:positions.id,positions.name');
                }
            ])
            ->orderBy('created_at')
            ->get();
        if (count($players)) {
            $results = $players->map(function ($item) {
                $obj = new \stdClass();
                $obj->id = $item->id;
                $obj->player_name = $item->first_name . ' ' . $item->last_name;
                $obj->profile_picture = $item->profile_picture;
                $obj->age = $item->age;
                $obj->gender = $item->gender;
                $obj->position = isset($item->player->position) ? [$item->player->position->name] : [];
                $obj->teams = $item->teams ?? [];
                return $obj;
            });
            return Helper::apiSuccessResponse(true, 'Records  found', $results);
        }
        return Helper::apiNotFoundResponse(false, 'Records  found', []);
    }*/



    /**
     * Get Teams
     *
     * @response{
    "Response": true,
    "StatusCode": 200,
    "Message": "Teams found",
    "Result": [
    {
    "id": 6,
    "team_name": "Test",
    "image": "",
    "gender": "mixed",
    "team_type": "indoor",
    "description": null,
    "age_group": "23",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2020-12-14 15:02:44",
    "updated_at": "2021-01-11 16:06:20",
    "deleted_at": null,
    "players_count": 5
    },
    {
    "id": 11,
    "team_name": "Team2",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "22",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2020-12-17 17:46:09",
    "updated_at": "2020-12-17 17:46:09",
    "deleted_at": null,
    "players_count": 0
    },
    {
    "id": 31,
    "team_name": "T1",
    "image": "",
    "gender": "man",
    "team_type": "field",
    "description": null,
    "age_group": "11",
    "min_age_group": 13,
    "max_age_group": 13,
    "created_at": "2021-01-12 14:07:25",
    "updated_at": "2021-01-12 14:07:25",
    "deleted_at": null,
    "players_count": 0
    }
    ]
    }
     *
     * @return JsonResponse
     */
  /*public function trainer_teams(){
      $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
      if(!$club){
          return Helper::apiErrorResponse(false, 'Club not found',new \stdClass());
      }
      $club_id = $club->club_id ?? 0;
      $teams = Team::whereHas('clubs',function($q) use ($club_id){
          return $q->where('club_id',$club_id);
      })->withCount('players')->get();
      if($teams->count()){
          return Helper::apiSuccessResponse(true, 'Teams found', $teams);
      }
      return Helper::apiSuccessResponse(false, 'Teams Not found', []);
  }*/
    /**
     * Search Teams/Players
     *
     * @response{
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
    "Result": {
    "teams": [
    {
    "id": 6,
    "team_name": "Test",
    "image": ""
    }
    ],
    "players": [
    {
    "id": 131,
    "first_name": "testing",
    "middle_name": "''",
    "last_name": null,
    "profile_picture": null,
    "age": null,
    "gender": null,
    "teams": [],
    "player": {
    "id": 60,
    "user_id": 131,
    "position_id": null,
    "position": null
    }
    },
    {
    "id": 212,
    "first_name": "Trainer",
    "middle_name": "''",
    "last_name": "Testing",
    "profile_picture": null,
    "age": null,
    "gender": null,
    "teams": [
    {
    "id": 6,
    "team_name": "Test",
    "image": "",
    "pivot": {
    "user_id": 212,
    "team_id": 6,
    "created_at": "2021-01-11 16:32:17"
    }
    }
    ],
    "player": null
    },
    {
    "id": 225,
    "first_name": "Tests it",
    "middle_name": "''",
    "last_name": "tes",
    "profile_picture": null,
    "age": "43",
    "gender": "man",
    "teams": [
    {
    "id": 5,
    "team_name": "consequatur",
    "image": "",
    "pivot": {
    "user_id": 225,
    "team_id": 5,
    "created_at": "2021-01-12 16:47:41"
    }
    }
    ],
    "player": {
    "id": 103,
    "user_id": 225,
    "position_id": null,
    "position": null
    }
    },
    {
    "id": 234,
    "first_name": "ABC",
    "middle_name": "''",
    "last_name": "Test",
    "profile_picture": null,
    "age": "19",
    "gender": "female",
    "teams": [
    {
    "id": 24,
    "team_name": "Team",
    "image": "",
    "pivot": {
    "user_id": 234,
    "team_id": 24,
    "created_at": "2021-01-13 14:17:48"
    }
    }
    ],
    "player": {
    "id": 110,
    "user_id": 234,
    "position_id": null,
    "position": null
    }
    },
    {
    "id": 324,
    "first_name": "ABC",
    "middle_name": "''",
    "last_name": "Test",
    "profile_picture": null,
    "age": "19",
    "gender": "female",
    "teams": [
    {
    "id": 36,
    "team_name": "Street 12",
    "image": "",
    "pivot": {
    "user_id": 324,
    "team_id": 36,
    "created_at": "2021-01-15 20:20:55"
    }
    }
    ],
    "player": {
    "id": 184,
    "user_id": 324,
    "position_id": null,
    "position": null
    }
    }
    ]
    }
    }
     *
     * @return JsonResponse
     */
  /*public function search(Request  $request){
        $search_results = [
            'teams'=>[],
            'players'=>[],
        ];
        $keyword = $request->keyword;
        //search in teams
      $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
      if($club){
          $club_id = $club->club_id ?? 0;
          $teams = Team::whereHas('clubs',function($q) use ($club_id){
              return $q->where('club_id',$club_id);
          })->where('team_name','LIKE','%'.$keyword.'%')->get();
          $search_results['teams']=$teams;
      }

      //search in players

      $clubs = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->get()->pluck('club_id');
      $players = User::role('player')
          ->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture', 'users.age', 'users.gender')
          ->with([
              'teams' => function ($q) {
                  $q->select('teams.id', 'teams.team_name', 'teams.image');
              },
          ])
          ->whereHas('clubs_players', function ($q) use ($clubs) {
              $q->whereIn('club_id', $clubs);
          })
          ->with([
              'player' => function ($q1) {
                  $q1->select('players.id', 'players.user_id', 'players.position_id');
                  $q1->with('position:positions.id,positions.name');
              }
          ])->where('first_name','LIKE',$keyword.'%')->orWhere('last_name','LIKE',$keyword.'%')
          ->orderBy('created_at')
          ->get();
      $search_results['players']=$players;

      return Helper::apiSuccessResponse(true, 'success', $search_results);
  }*/







}