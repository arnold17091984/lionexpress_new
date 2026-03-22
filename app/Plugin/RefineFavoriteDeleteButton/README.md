# RefineFavoriteDeleteButton

EC-CUBE4向け
商品詳細ページお気に入り削除ボタンプラグインです。

## テンプレートの編集

プラグインインストール後、
管理画面 > コンテンツ管理 > ページ管理 > 商品詳細ページ
編集画面にて

```
                    {% if BaseInfo.option_favorite_product %}
                        <form action="{{ url('product_add_favorite', {id:Product.id}) }}" method="post">
                            <div class="ec-productRole__btn">
                                {% if is_favorite == false %}
                                    <button type="submit" id="favorite" class="ec-blockBtn--cancel">
                                        {{ 'お気に入りに追加'|trans }}
                                    </button>
                                {% else %}
                                    <button type="submit" id="favorite" class="ec-blockBtn--cancel"
                                            disabled="disabled">{{ 'お気に入りに追加済です。'|trans }}
                                    </button>
                                {% endif %}
                            </div>
                        </form>
                    {% endif %}
```

を下記に書き換えてください。

```
                    {% if BaseInfo.option_favorite_product %}
                        {% if is_favorite == false %}
                            <form action="{{ url('product_add_favorite', {id:Product.id}) }}" method="post">
                                <div class="ec-productRole__btn">
                                    <button type="submit" id="favorite" class="ec-blockBtn--cancel">
                                        {{ 'お気に入りに追加'|trans }}
                                    </button>
                                </div>
                            </form>
                        {% else %}
                            <form action="{{ url('refine_delete_favorite', {id:Product.id}) }}" method="post">
                                <div class="ec-productRole__btn">
                                    <button type="submit" id="favorite" class="ec-blockBtn--cancel">
                                        {{ 'お気に入りから削除'|trans }}
                                    </button>
                                </div>
                            </form>
                        {% endif %}
                    {% endif %}
```
