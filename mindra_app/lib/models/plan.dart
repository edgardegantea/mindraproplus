class Plan {
  final int id;
  final String slug;
  final String name;
  final String description;
  final int priceCents;
  final String currency;
  final Map<String, dynamic> features;
  final int trialDays;

  const Plan({
    required this.id,
    required this.slug,
    required this.name,
    required this.description,
    required this.priceCents,
    required this.currency,
    required this.features,
    required this.trialDays,
  });

  factory Plan.fromJson(Map<String, dynamic> json) => Plan(
        id: json['id'] as int,
        slug: json['slug'] as String,
        name: json['name'] as String,
        description: json['description'] as String,
        priceCents: json['price_cents'] as int,
        currency: json['currency'] as String,
        features: Map<String, dynamic>.from(json['features'] as Map),
        trialDays: json['trial_days'] as int,
      );

  bool get isFree => slug == 'free';
  bool get isPro  => slug == 'pro';
  bool get isPlus => slug == 'plus';

  bool hasFeature(String key) => features[key] == true;

  String get formattedPrice {
    if (isFree) return '\$0.00 MXN';
    if (isPlus) return 'A medida';
    final amount = (priceCents / 100).toStringAsFixed(2);
    return '\$$amount $currency/mes';
  }
}
