if(Array.prototype.includes === undefined) {
	Array.prototype.includes = function(searchElement, fromIndex) {
		let len = this.length >>> 0;
		if(len === 0) {
			return false;
		}
		
		let n = fromIndex | 0;
		let k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);
		
		function areSameValue(x, y) {
			return x === y || (typeof x === 'number' && typeof y === 'number' && isNaN(x) && isNaN(y));
		}
		
		for(; k < len; k++) {
			if(areSameValue(this[k], searchElement)) {
				return true;
			}
		}
		
		return false;
	};
}
