// ═══════════════════ DATA ═══════════════════

// Mock data for dashboard
export let products = [
  {id:1,title:'Vanilla Amber Dream',category:'Aromatherapy',scent:'Vanilla, Amber, Musk',price:340,stock:45,status:'In Stock'},
  {id:2,title:'Lavender Mist',category:'Aromatherapy',scent:'Lavender, Chamomile',price:300,stock:30,status:'In Stock'},
  {id:3,title:'Rose Garden Bloom',category:'Gift Sets',scent:'Rose, Peony, Sandalwood',price:420,stock:3,status:'Low Stock'},
  {id:4,title:'Sandalwood Dusk',category:'Soy Candles',scent:'Sandalwood, Cedar, Vanilla',price:400,stock:2,status:'Low Stock'},
  {id:5,title:'Coconut & Lime Zest',category:'Seasonal',scent:'Coconut, Lime, Sea Salt',price:300,stock:0,status:'Out of Stock'},
  {id:6,title:'Spiced Cinnamon',category:'Seasonal',scent:'Cinnamon, Clove, Orange',price:320,stock:1,status:'Low Stock'},
  {id:7,title:'Midnight Jasmine',category:'Aromatherapy',scent:'Jasmine, White Tea',price:360,stock:25,status:'In Stock'},
  {id:8,title:'Honey & Oat Gift Set',category:'Gift Sets',scent:'Honey, Oat, Warm Milk',price:680,stock:18,status:'In Stock'},
];

export let categories = [
  {id:1,name:'Aromatherapy',slug:'aromatherapy',products:48,created:'Jan 5, 2024'},
  {id:2,name:'Gift Sets',slug:'gift-sets',products:36,created:'Jan 5, 2024'},
  {id:3,name:'Seasonal',slug:'seasonal',products:28,created:'Feb 14, 2024'},
  {id:4,name:'Soy Candles',slug:'soy-candles',products:22,created:'Feb 20, 2024'},
  {id:5,name:'Pillar & Taper',slug:'pillar-taper',products:14,created:'Mar 1, 2024'},
];

export let customers = [];

export let admins = [
  {id:1,name:'Solis Admin',email:'admin@solis.com',role:'Super Admin',lastLogin:'Apr 12, 2025 09:00 AM'},
];